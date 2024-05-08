<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\HostUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use Google\Service\PeopleService;
use Google\Service\PeopleService\Address;
use Google\Service\PeopleService\BatchCreateContactsRequest;
use Google\Service\PeopleService\BatchUpdateContactsRequest;
use Google\Service\PeopleService\Birthday;
use Google\Service\PeopleService\ContactGroupMembership;
use Google\Service\PeopleService\ContactToCreate;
use Google\Service\PeopleService\Date;
use Google\Service\PeopleService\EmailAddress;
use Google\Service\PeopleService\FieldMetadata;
use Google\Service\PeopleService\Gender;
use Google\Service\PeopleService\Membership;
use Google\Service\PeopleService\Name;
use Google\Service\PeopleService\Nickname;
use Google\Service\PeopleService\Person;
use Google\Service\PeopleService\ContactGroup;
use Google\Service\PeopleService\CreateContactGroupRequest;
use Google\Service\PeopleService\PhoneNumber;
use Google\Service\PeopleService\UpdateContactPhotoRequest;
use Google\Service\PeopleService\Url;
use Google\Service\PeopleService\UserDefined;
use Google_Service_Exception;

/**
 * Documentatie: https://developers.google.com/people/api/rest
 */
class GoogleContactSync
{
	private const DEFAULT_GROEPNAAM = 'C.S.R.-leden';
	private const READ_MASK = 'userDefined';
	private const UPDATE_MASK = 'names,nicknames,genders,birthdays,addresses,phoneNumbers,emailAddresses,urls,userDefined';

	/**
	 * @var GoogleAuthenticator
	 */
	private $authenticator;
	/**
	 * @var string
	 */
	private $groepNaam;
	/**
	 * @var PeopleService
	 */
	private $peopleService;
	/**
	 * @var string[]
	 */
	private $currentContactMap = [];
	/**
	 * @var string[]
	 */
	private $currentEtagMap = [];
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var string
	 */
	private $csrGroupResourceName;
	/**
	 * @var bool
	 */
	private $initialized = false;

	public function __construct(
		GoogleAuthenticator $authenticator,
		ProfielRepository $profielRepository
	) {
		$this->authenticator = $authenticator;

		$this->groepNaam = trim(
			InstellingUtil::lid_instelling('googleContacts', 'groepnaam')
		);
		if (empty($this->groepNaam)) {
			$this->groepNaam = self::DEFAULT_GROEPNAAM;
		}
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @return ContactGroup[] Alle ContactGroups van gebruiker.
	 */
	private function getContactGroups(): array
	{
		$pageToken = null;
		$groups = [];
		$max = 1;

		while (count($groups) < $max) {
			$options = [
				'pageSize' => 2,
				'groupFields' => 'name,memberCount',
			];
			if ($pageToken) {
				$options['pageToken'] = $pageToken;
			}

			$contactGroups = $this->peopleService->contactGroups->listContactGroups(
				$options
			);
			$groups = array_merge($groups, $contactGroups->getContactGroups());
			$pageToken = $contactGroups->getNextPageToken();
			$max = $contactGroups->getTotalItems();
		}

		return $groups;
	}

	/**
	 * Haal ContactGroup met ingestelde naam op.
	 * @return false|ContactGroup
	 */
	private function getCSRContactGroup(): ContactGroup|bool
	{
		foreach ($this->getContactGroups() as $group) {
			if ($group->getName() === $this->groepNaam) {
				return $group;
			}
		}

		return false;
	}

	/**
	 * Haal ContactGroup met ingestelde naam op. Maakt nieuwe aan als deze niet bestaat.
	 * @return false|ContactGroup
	 */
	private function getOrCreateCSRContactGroup(): false|ContactGroup
	{
		$group = $this->getCSRContactGroup();
		if (!$group) {
			$contactGroup = new ContactGroup();
			$contactGroup->setName($this->groepNaam);
			$createContactGroupRequest = new CreateContactGroupRequest();
			$createContactGroupRequest->setContactGroup($contactGroup);
			$group = $this->peopleService->contactGroups->create(
				$createContactGroupRequest
			);
		}

		$this->csrGroupResourceName = $group->getResourceName();
		return $group;
	}

	/**
	 * @return string[] Lijst van resource ID's van contacten in C.S.R. groep
	 */
	private function getCSRContactGroupMemberIDs(): array
	{
		$contactGroup = $this->getOrCreateCSRContactGroup();
		$contactGroupDetails = $this->peopleService->contactGroups->get(
			$contactGroup->getResourceName(),
			[
				'maxMembers' => 100000,
			]
		);

		return $contactGroupDetails->getMemberResourceNames() ?: [];
	}

	/**
	 * @return Person[] Contacten in C.S.R. lijst.
	 */
	private function getCurrentContacts(): array
	{
		$ids = $this->getCSRContactGroupMemberIDs();
		$contacts = [];

		foreach (array_chunk($ids, 200) as $chunkIds) {
			$getPeopleResponse = $this->peopleService->people->getBatchGet([
				'resourceNames' => $chunkIds,
				'personFields' => self::READ_MASK,
			]);

			foreach ($getPeopleResponse->getResponses() as $response) {
				if ($response->getHttpStatusCode() === 200) {
					$contacts[] = $response->getPerson();
				}
			}
		}

		return $contacts;
	}

	/**
	 * @param Person $contact
	 * @return string|null
	 */
	private static function getContactCsrUid(Person $contact): ?string
	{
		$velden = $contact->getUserDefined();

		if (!is_array($velden)) {
			return null;
		}

		foreach ($velden as $property) {
			if ($property->getKey() === 'csruid') {
				return $property->getValue();
			}
		}

		return null;
	}

	private function loadCurrentContacts(): void
	{
		$contacts = $this->getCurrentContacts();
		foreach ($contacts as $contact) {
			$csrUid = self::getContactCsrUid($contact);
			if ($csrUid) {
				$this->currentContactMap[$csrUid] = $contact->getResourceName();
				$this->currentEtagMap[$csrUid] = $contact->getEtag();
			}
		}
	}

	/**
	 * @param Profiel $profiel
	 * @return Person
	 *
	 * Let op: voeg bij aanpassing veld toe bij constante UPDATE_MASK.
	 */
	private function convertProfielToPerson(Profiel $profiel): Person
	{
		$person = new Person();
		if (array_key_exists($profiel->uid, $this->currentContactMap)) {
			$person->setResourceName($this->currentContactMap[$profiel->uid]);
			$person->setEtag($this->currentEtagMap[$profiel->uid]);
		} else {
			// membership
			$csrGroupMembership = new Membership();
			$contactGroupMembership = new ContactGroupMembership();
			$contactGroupMembership->setContactGroupResourceName(
				$this->csrGroupResourceName
			);
			$csrGroupMembership->setContactGroupMembership($contactGroupMembership);
			$person->setMemberships([$csrGroupMembership]);
		}

		// names
		$name = new Name();
		$name->setGivenName(
			trim(
				!empty($profiel->voornaam) ? $profiel->voornaam : $profiel->voorletters
			)
		);
		$name->setMiddleName(trim($profiel->tussenvoegsel));
		$name->setFamilyName(trim($profiel->achternaam));
		$person->setNames([$name]);

		// nicknames
		$nicknames = [];

		$civitasNaam = new Nickname();
		$civitasNaam->setValue($profiel->getNaam('civitas'));
		$nicknames[] = $civitasNaam;

		if ($profiel->nickname) {
			$bijnaam = new Nickname();
			$bijnaam->setValue($profiel->nickname);
			$nicknames[] = $bijnaam;
		}

		$person->setNicknames($nicknames);

		// genders
		if ($profiel->geslacht) {
			$gender = new Gender();
			$gender->setValue(
				$profiel->geslacht == Geslacht::Man ? 'male' : 'female'
			);
			$person->setGenders([$gender]);
		}

		// birthdays
		if (
			$profiel->gebdatum &&
			DateUtil::dateFormatIntl($profiel->gebdatum, DateUtil::DATE_FORMAT) !=
				'0000-00-00'
		) {
			$birthday = new Birthday();
			$birthdayDate = new Date();
			$birthdayDate->setDay($profiel->gebdatum->format('j'));
			$birthdayDate->setMonth($profiel->gebdatum->format('n'));
			$birthdayDate->setYear($profiel->gebdatum->format('Y'));
			$birthday->setDate($birthdayDate);
			$person->setBirthdays([$birthday]);
		}

		// addresses
		$addresses = [];
		if ($profiel->adres) {
			$address = new Address();
			$metadata = new FieldMetadata();
			$metadata->setSourcePrimary(true);
			$address->setMetadata($metadata);

			$woonoord = $profiel->getWoonoord();
			if ($woonoord) {
				$address->setType($woonoord->naam);
			} else {
				$address->setType('home');
			}

			$address->setStreetAddress($profiel->adres);
			$address->setPostalCode($profiel->postcode ?: null);
			$address->setCity($profiel->woonplaats);
			$address->setCountry($profiel->land ?: null);
			$address->setFormattedValue($profiel->getFormattedAddress());
			$addresses[] = $address;
		}

		if (
			$profiel->o_adres &&
			(!$profiel->adres ||
				InstellingUtil::lid_instelling('googleContacts', 'ouderAdres') === 'ja')
		) {
			$address = new Address();
			$metadata = new FieldMetadata();
			$metadata->setSourcePrimary(!$profiel->adres);
			$address->setMetadata($metadata);
			$address->setType('Ouders');
			$address->setStreetAddress($profiel->o_adres);
			$address->setPostalCode($profiel->o_postcode ?: null);
			$address->setCity($profiel->o_woonplaats);
			$address->setCountry($profiel->o_land ?: null);
			$address->setFormattedValue($profiel->getFormattedAddressOuders());
			$addresses[] = $address;
		}

		$person->setAddresses($addresses);

		// phoneNumbers
		$phoneNumberList = [
			['mobiel', 'mobile', true],
			['telefoon', 'home', false],
		];

		if (
			InstellingUtil::lid_instelling(
				'googleContacts',
				'ouderTelefoonnummer'
			) === 'ja'
		) {
			$phoneNumberList[] = ['o_telefoon', 'Ouders', false];
		}

		$phoneNumbers = [];

		foreach ($phoneNumberList as $pn) {
			if ($profiel->{$pn[0]}) {
				$phoneNumber = new PhoneNumber();
				$phoneNumber->setValue(
					$this->internationalizePhonenumber($profiel->{$pn[0]})
				);
				$phoneNumber->setType($pn[1]);
				if ($pn[2]) {
					$fieldMetadata = new FieldMetadata();
					$fieldMetadata->setSourcePrimary(true);
					$phoneNumber->setMetadata($fieldMetadata);
				}

				$phoneNumbers[] = $phoneNumber;
			}
		}

		$person->setPhoneNumbers($phoneNumbers);

		// emailAddresses
		$emailList = [['email', 'home', true], ['sec_email', 'other', false]];
		$emailAddresses = [];

		foreach ($emailList as $email) {
			if ($profiel->{$email[0]}) {
				$emailAddress = new EmailAddress();
				$emailAddress->setValue($profiel->{$email[0]});
				$emailAddress->setType($email[1]);
				if ($email[2]) {
					$fieldMetadata = new FieldMetadata();
					$fieldMetadata->setSourcePrimary(true);
					$emailAddress->setMetadata($fieldMetadata);
				}

				$emailAddresses[] = $emailAddress;
			}
		}

		$person->setEmailAddresses($emailAddresses);

		// urls
		$urlList = [
			[
				HostUtil::getCsrRoot() . '/profiel/' . $profiel->uid,
				'C.S.R. webstek profiel',
				true,
			],
			[$profiel->website, 'Website', false],
			[$profiel->linkedin, 'LinkedIn', false],
		];
		$urls = [];

		foreach ($urlList as $urlEntry) {
			if ($urlEntry[0]) {
				$url = new Url();
				$url->setValue($urlEntry[0]);
				$url->setType($urlEntry[1]);
				if ($urlEntry[2]) {
					$fieldMetadata = new FieldMetadata();
					$fieldMetadata->setSourcePrimary(true);
					$url->setMetadata($fieldMetadata);
				}

				$urls[] = $url;
			}
		}

		$person->setUrls($urls);

		// userDefined
		$update = new UserDefined();
		$update->setKey('update');
		$update->setValue(DateUtil::getDateTime());

		$csrUid = new UserDefined();
		$csrUid->setKey('csruid');
		$csrUid->setValue($profiel->uid);

		$person->setUserDefined([$update, $csrUid]);

		return $person;
	}

	/**
	 * @param Profiel[] $profielen Profielen om aan te maken.
	 * @return void
	 */
	private function createContacts(array $profielen): void
	{
		// Max. 200 per request
		foreach (array_chunk($profielen, 200) as $toInsert) {
			// Bouw request
			$batchCreateContactsRequest = new BatchCreateContactsRequest();
			$contacts = array_map(function (Profiel $profiel) {
				$contactPerson = $this->convertProfielToPerson($profiel);
				$contactToCreate = new ContactToCreate();
				$contactToCreate->setContactPerson($contactPerson);
				return $contactToCreate;
			}, $toInsert);
			$batchCreateContactsRequest->setContacts($contacts);

			// Maak contacten aan
			$batchCreateContactsRequest->setReadMask(self::READ_MASK);
			$inserted = $this->peopleService->people->batchCreateContacts(
				$batchCreateContactsRequest
			);

			// Zet resourceNames in currentContactMap
			foreach ($inserted->getCreatedPeople() as $created) {
				if ($created->getHttpStatusCode() === 200) {
					$uid = self::getContactCsrUid($created->getPerson());
					if ($uid) {
						$this->currentContactMap[$uid] = $created
							->getPerson()
							->getResourceName();
					}
				}
			}
		}
	}

	/**
	 * @param Profiel[] $profielen Profielen om te updaten.
	 * @return void
	 */
	private function updateContacts(array $profielen): void
	{
		// Max. 200 per request
		foreach (array_chunk($profielen, 200) as $toUpdate) {
			// Bouw request
			$batchUpdateContactsRequest = new BatchUpdateContactsRequest();

			/** @var Person[] $contacts */
			$contacts = [];
			foreach ($toUpdate as $profiel) {
				$person = $this->convertProfielToPerson($profiel);
				$contacts[$person->getResourceName()] = $person;
			}
			$batchUpdateContactsRequest->setContacts($contacts);

			// Update contacten
			$batchUpdateContactsRequest->setUpdateMask(self::UPDATE_MASK);
			$batchUpdateContactsRequest->setReadMask(self::READ_MASK);
			$this->peopleService->people->batchUpdateContacts(
				$batchUpdateContactsRequest
			);
		}
	}

	/**
	 * @param Profiel[] $profielBatch
	 * @return void
	 */
	private function updatePhotos(array $profielBatch): void
	{
		foreach ($profielBatch as $profiel) {
			$pasfotoPath = $profiel->getPasfotoInternalPath('vierkant');
			if ($pasfotoPath) {
				$resourceId = $this->currentContactMap[$profiel->uid];
				$photoBytes = base64_encode(file_get_contents($pasfotoPath));
				$updateContactPhotoRequest = new UpdateContactPhotoRequest();
				$updateContactPhotoRequest->setPhotoBytes($photoBytes);

				$this->peopleService->people->updateContactPhoto(
					$resourceId,
					$updateContactPhotoRequest
				);
			}
		}
	}

	/**
	 * @param string $redirectURL
	 * @return void
	 */
	public function initialize(string $redirectURL): void
	{
		if ($this->initialized) {
			return;
		}
		$this->authenticator->doRequestToken($redirectURL);
		$client = $this->authenticator->createClient();
		$token = $this->authenticator->getToken()->token;
		$client->fetchAccessTokenWithRefreshToken($token);
		$this->peopleService = new PeopleService($client);

		try {
			$this->loadCurrentContacts();
			$this->initialized = true;
		} catch (CsrException $e) {
			$this->authenticator->deleteToken();
			throw new CsrGebruikerException('Google synchronisatie mislukt');
		}
	}

	/**
	 * @param Profiel|string $lid
	 * @return string
	 */
	public function syncLid($lid): string
	{
		return $this->syncLidBatch([$lid]);
	}

	/**
	 * @param Profiel[]|string[] $leden
	 * @return string
	 */
	public function syncLidBatch(array $leden): string
	{
		try {
			// Maak lijst van profielen
			/** @var Profiel[] $profielBatch */
			$profielBatch = array_filter(
				array_map(function ($profiel) {
					return $profiel instanceof Profiel
						? $profiel
						: $this->profielRepository->find($profiel);
				}, $leden)
			);

			// Bepaal inserts/updates
			$toUpdate = [];
			$toInsert = [];
			foreach ($profielBatch as $profiel) {
				if (array_key_exists($profiel->uid, $this->currentContactMap)) {
					$toUpdate[] = $profiel;
				} else {
					$toInsert[] = $profiel;
				}
			}

			// Maak nieuwe contacten aan
			if (!empty($toInsert)) {
				$this->createContacts($toInsert);
			}

			// Update bestaande contacten
			if (!empty($toUpdate)) {
				$this->updateContacts($toUpdate);
			}

			// Upload foto's
			// Disabled: Google staat standaard maar 60 uploads per seconde toe
			//$this->updatePhotos($profielBatch);

			// Stel melding in
			$aantalAangemaakt = count($toInsert);
			$meervoudAangemaakt = $aantalAangemaakt === 1 ? 'contact' : 'contacten';
			$aantalBijgewerkt = count($toUpdate);
			$meervoudBijgewerkt = $aantalBijgewerkt === 1 ? 'contact' : 'contacten';
			return "$aantalAangemaakt $meervoudAangemaakt aangemaakt, $aantalBijgewerkt $meervoudBijgewerkt bijgewerkt";
		}/** @noinspection PhpRedundantCatchClauseInspection */  catch (Google_Service_Exception $exception) {
			$exceptionJson = json_decode($exception->getMessage());
			if (
				json_last_error() === JSON_ERROR_NONE &&
				isset($exceptionJson->error) &&
				isset($exceptionJson->error->message)
			) {
				throw new CsrGebruikerException(
					'Google sync mislukt: ' . $exceptionJson->error->message
				);
			} else {
				throw new CsrGebruikerException('Google sync mislukt');
			}
		}
	}

	/**
	 * Voeg landcode toe als nummer met 0 begint of vervang 00 met +
	 *
	 * @param string $phonenumber
	 * @param string $prefix
	 *
	 * @return string
	 */
	private function internationalizePhonenumber($phonenumber, $prefix = '+31'): string
	{
		$number = str_replace([' ', '-'], '', $phonenumber);
		if ($number[0] === '0') {
			// vergelijken met == 0 levert problemen op want (int) '+' = 0 dankzij php
			if ($number[1] === '0') {
				return '+' . substr($number, 2);
			}
			return $prefix . substr($number, 1);
		} else {
			return $phonenumber;
		}
	}
}
