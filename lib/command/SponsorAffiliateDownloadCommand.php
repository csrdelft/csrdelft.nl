<?php

namespace CsrDelft\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * sponsorlinks_affiliate_download.php
 *
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @since 26/10/2017
 */
class SponsorAffiliateDownloadCommand extends Command {
	protected static $defaultName = 'stek:sponsor:download';
	/**
	 * @var string
	 */
	private $sponsorSlHost;
	/**
	 * @var string
	 */
	private $sponsorClubId;
	/**
	 * @var string
	 */
	private $sponsorUserAgent;

	public function __construct(string $sponsorSlHost, string $sponsorClubId, string $sponsorUserAgent) {
		parent::__construct();
		$this->sponsorSlHost = $sponsorSlHost;
		$this->sponsorClubId = $sponsorClubId;
		$this->sponsorUserAgent = $sponsorUserAgent;
	}

	protected function configure() {
		$this
			->setDescription('Download sponsorlinks');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$output->writeln("Download sponsorlinks");

		if (!$this->sponsorSlHost || !$this->sponsorClubId || !$this->sponsorUserAgent) {
			$output->writeln("Zorg ervoor dat SPONSOR_SL_HOST, SPONSOR_CLUBID en SPONSOR_USERAGENT gezet zijn in .env");

			return Command::FAILURE;
		}

		//Steps
		$PAGE_URL = $this->sponsorSlHost . '/api/?call=webshops_club_extension&club=';

		$scrapeUrl = $PAGE_URL . $this->sponsorClubId;

		//1. GET JSON
		$result = curl_request($scrapeUrl, [CURLOPT_USERAGENT => $this->sponsorUserAgent . "a"]);

		$webshops = json_decode($result)->webshops;

		//3. Follow links to final destination
		$data = ["club_id" => $this->sponsorClubId];
		$affiliates = [];
		$amount = 0;
		foreach ($webshops as $webshop) {
			if ($webshop->extension == "0") {
				continue;
			}

			if ($webshop->orig_url == "") {
				continue;
			}

			preg_match('/shop_id=(\d+)/', $webshop->link, $shopId);
			$entry = [
				"shop_name" => $webshop->name_short,
				"shop_name_long" => $webshop->name_long,
				"shop_category" => $webshop->category,
				"shop_id" => $shopId[1],
				"shop_price" => $webshop->commission_gross,
				"shop_description" => $webshop->description,
				"shop_logo" => $webshop->logo_120x60
			];

			$host = $webshop->orig_url;
			if (array_key_exists($host, $affiliates)) {
				$affiliates[$host][] = $entry;
			} else {
				$affiliates[$host] = [$entry];
			}

			$amount++;
		}

		// Store affiliates map in (soon to be JSON) output data
		$data["affiliates"] = $affiliates;

		// 4. Save results to sponsorlinks.json in data folder (overwriting existing)
		$outputFile = fopen(DATA_PATH . 'sponsorlinks.json', 'w');
		fwrite($outputFile, json_encode($data));
		fclose($outputFile);

		return Command::SUCCESS;
	}
}
