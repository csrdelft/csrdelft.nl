<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\Entity\FotoAlbumVoter;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\view\fotoalbum\FotoAlbumBreadcrumbs;
use CsrDelft\view\fotoalbum\FotoAlbumToevoegenForm;
use CsrDelft\view\fotoalbum\FotosDropzone;
use CsrDelft\view\fotoalbum\FotoTagToevoegenForm;
use CsrDelft\view\fotoalbum\PosterUploadForm;
use CsrDelft\view\Icon;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FotoAlbumController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het fotoalbum.
 */
class FotoAlbumController extends AbstractController
{
	/**
	 * @var FotoAlbumRepository
	 */
	private $fotoAlbumRepository;
	/**
	 * @var FotoTagsRepository
	 */
	private $fotoTagsRepository;
	/**
	 * @var FotoRepository
	 */
	private $fotoRepository;

	public function __construct(
		FotoTagsRepository $fotoTagsRepository,
		FotoAlbumRepository $fotoAlbumRepository,
		FotoRepository $fotoRepository
	) {
		$this->fotoTagsRepository = $fotoTagsRepository;
		$this->fotoAlbumRepository = $fotoAlbumRepository;
		$this->fotoRepository = $fotoRepository;
	}

	/**
	 * @param $dir
	 * @return RedirectResponse
	 * @Route("/fotoalbum/verwerken/{dir}", methods={"GET"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_MOD,P_ALBUM_PUBLIC_MOD})
	 */
	public function verwerken($dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::AANPASSEN, $album);
		if ($album->dirname === 'fotoalbum') {
			MeldingUtil::setMelding('Niet het complete fotoalbum verwerken', -1);
		} else {
			$this->fotoAlbumRepository->verwerkFotos($album);
		}
		return $this->redirectToRoute('csrdelft_fotoalbum_bekijken', [
			'dir' => $dir,
		]);
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return FotoAlbumToevoegenForm|JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/fotoalbum/toevoegen/{dir}", methods={"POST"}, requirements={"dir": ".+"}, defaults={"dir": ""})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function toevoegen(Request $request, $dir)
	{
		$album = new FotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::TOEVOEGEN, $album);

		$formulier = new FotoAlbumToevoegenForm($album);
		if ($request->getMethod() == 'POST' && $formulier->validate()) {
			$subalbum = $formulier->findByName('subalbum')->getValue();
			$album->path = PathUtil::join_paths($album->path, $subalbum);
			$album->subdir = PathUtil::join_paths($album->subdir, $subalbum);
			if (!$album->exists()) {
				$this->fotoAlbumRepository->create($album);
			}
			return new JsonResponse($album->getUrl());
		}
		return $formulier;
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse|RedirectResponse|Response
	 * @Route("/fotoalbum/uploaden/{dir}", methods={"GET","POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function uploaden(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::TOEVOEGEN, $album);

		$poster = $album->dirname === 'Posters';
		if ($poster) {
			$formulier = new PosterUploadForm($album);
			$uploader = $formulier->findByName('afbeelding');
		} else {
			$formulier = new FotosDropzone($album);
			$uploader = $formulier->getPostedUploader();
		}
		if ($request->getMethod() == 'POST') {
			if ($formulier->validate()) {
				try {
					if ($poster) {
						$filename =
							$formulier->findByName('posternaam')->getValue() . '.jpg';
						if (strpos($filename, 'folder') !== false) {
							throw new CsrGebruikerException('Albumcover niet toegestaan');
						}
					} else {
						$filename = $uploader->getModel()->filename;
					}
					$uploader->opslaan($album->path, $filename);
					$foto = new Foto($filename, $album);
					// opslaan gelukt?
					if (!$foto->exists()) {
						throw new CsrGebruikerException('Opslaan mislukt');
					}
					$this->fotoRepository->verwerkFoto($foto);
					// verwerken gelukt?
					if (!$foto->isComplete()) {
						throw new CsrGebruikerException('Verwerken mislukt');
					}

					if ($poster) {
						return $this->redirectToRoute('csrdelft_fotoalbum_bekijken', [
							'dir' => $dir,
							'_fragment' => $foto->getResizedUrl(),
						]);
					} else {
						return new JsonResponse(true);
					}
				} catch (CsrGebruikerException $e) {
					return new JsonResponse(['error' => $e->getMessage()], 500);
				}
			} else {
				if (!$poster && $uploader !== null) {
					return new JsonResponse(['error' => $uploader->getError()], 500);
				}
			}
		}
		return $this->render('default.html.twig', ['content' => $formulier]);
	}

	/**
	 * @param $dir
	 * @return JsonResponse
	 * @Route("/fotoalbum/bestaande/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function bestaande($dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::TOEVOEGEN, $album);

		$list = [];
		$files = $album->getFotos();
		if ($files !== false) {
			foreach ($files as $filename) {
				$afbeelding = new Afbeelding($filename->getThumbPath());
				if (str_ends_with($afbeelding->filename, '.jpg')) {
					$obj = [];
					$obj['name'] = $afbeelding->filename;
					$obj['size'] = $afbeelding->filesize;
					$obj['type'] = $afbeelding->mimetype;
					$obj['thumbnail'] = $filename->getThumbUrl();
					$list[] = $obj;
				}
			}
		}
		return new JsonResponse($list);
	}

	/**
	 * @param $dir
	 * @Route("/fotoalbum/downloaden/{dir}", methods={"GET"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_DOWN,P_ALBUM_PUBLIC_READ})
	 */
	public function downloaden($dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::DOWNLOADEN, $album);
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-tar');
		header(
			'Content-Disposition: attachment; filename="' . $album->dirname . '.tar"'
		);
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		$fotos = $album->getFotos();
		set_time_limit(0);
		$cmd = 'tar cC ' . escapeshellarg($album->path);
		foreach ($fotos as $foto) {
			$cmd .= ' ' . escapeshellarg($foto->filename);
		}
		$fh = popen($cmd, 'r');
		while (!feof($fh)) {
			print fread($fh, 8192);
		}
		pclose($fh);
		exit();
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse
	 * @Route("/fotoalbum/hernoemen/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_MOD,P_ALBUM_PUBLIC_ADD})
	 */
	public function hernoemen(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::AANPASSEN, $album);
		$naam = trim($request->request->get('naam'));
		$naam = str_replace('..', '', $naam);

		try {
			$this->fotoAlbumRepository->hernoemAlbum($album, $naam);
		} catch (CsrException $exception) {
			return new JsonResponse($exception->getMessage(), 400);
		}
		return new JsonResponse($album->getUrl());
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse
	 * @Route("/fotoalbum/albumcover/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function albumcover(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::AANPASSEN, $album);
		$filename = $request->request->get('foto');
		$cover = new Foto($filename, $album);
		if (
			$cover->exists() &&
			$this->fotoAlbumRepository->setAlbumCover($album, $cover)
		) {
			return new JsonResponse($album->getUrl() . '#' . $cover->getResizedUrl());
		} else {
			return new JsonResponse('Fotoalbum-cover instellen mislukt', 500);
		}
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse
	 * @Route("/fotoalbum/verwijderen/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function verwijderen(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::VERWIJDEREN, $album);

		if ($album->isEmpty()) {
			try {
				$this->fotoAlbumRepository->delete($album);
				MeldingUtil::setMelding('Fotoalbum verwijderen geslaagd', 1);
				return new JsonResponse(dirname($album->getUrl()));
			} catch (ORMException $ex) {
				MeldingUtil::setMelding('Fotoalbum verwijderen mislukt', -1);
				return new JsonResponse($album->getUrl());
			}
		}
		$filename = $request->request->get('foto');

		$foto = $this->fotoRepository->get($album->subdir, $filename);
		if ($this->fotoRepository->verwijderFoto($foto)) {
			echo '<div id="' . md5($filename) . '" class="remove"></div>';
			exit();
		} else {
			return new JsonResponse('Foto verwijderen mislukt', 500);
		}
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/fotoalbum/roteren/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth({P_ALBUM_ADD,P_ALBUM_PUBLIC_ADD})
	 */
	public function roteren(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::AANPASSEN, $album);
		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		$degrees = $request->request->getInt('rotation');
		$this->fotoRepository->rotate($foto, $degrees);
		return new JsonResponse(true);
	}

	/**
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Route("/fotoalbum/zoeken", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function zoeken(Request $request, $zoekterm = null)
	{
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}

		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$query = iconv('utf-8', 'ascii//TRANSLIT', $zoekterm); // convert accented characters to regular
		$limit = $request->query->getInt('limit', 5);
		$result = [];
		foreach ($this->fotoAlbumRepository->zoeken($query, $limit) as $album) {
			/** @var FotoAlbum $album */
			$result[] = [
				'icon' => Icon::getTag('fotoalbum', null, 'Fotoalbum', 'me-2'),
				'url' => $album->getUrl(),
				'label' => $album->getParentName(),
				'value' => ucfirst($album->dirname),
			];
		}
		return new JsonResponse($result);
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return JsonResponse
	 * @Route("/fotoalbum/gettags/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function gettags(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw $this->createAccessDeniedException();
		}
		// return all tags
		$tags = $this->fotoTagsRepository->getTags($foto);
		return new JsonResponse($tags);
	}

	/**
	 * @param Request $request
	 * @param $dir
	 * @return FotoTagToevoegenForm|JsonResponse
	 * @Route("/fotoalbum/addtag/{dir}", methods={"POST"}, requirements={"dir": ".+"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function addtag(Request $request, $dir)
	{
		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::TOEVOEGEN, $album);

		$filename = $request->request->get('foto');
		$foto = new Foto($filename, $album);
		if (!$foto->exists()) {
			throw $this->createAccessDeniedException();
		}
		$formulier = new FotoTagToevoegenForm($foto);
		if ($request->getMethod() == 'POST' && $formulier->validate()) {
			$uid = $formulier->findByName('uid')->getValue();
			$x = $formulier->findByName('x')->getValue();
			$y = $formulier->findByName('y')->getValue();
			$size = $formulier->findByName('size')->getValue();
			$this->fotoTagsRepository->addTag($foto, $uid, $x, $y, $size);
			// return all tags
			$tags = $this->fotoTagsRepository->getTags($foto);
			return new JsonResponse($tags);
		} else {
			return $formulier;
		}
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/fotoalbum/removetag", methods={"POST"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function removetag(Request $request)
	{
		$refuuid = $request->request->get('refuuid');
		$keyword = $request->request->get('keyword');
		if (!$this->mag(P_ALBUM_MOD) && !$this->mag($keyword)) {
			throw $this->createAccessDeniedException();
		}
		$this->fotoTagsRepository->removeTag($refuuid, $keyword);
		/** @var Foto $foto */
		$foto = $this->fotoRepository->retrieveByUUID($refuuid);
		if ($foto) {
			// return all tags
			$tags = $this->fotoTagsRepository->getTags($foto);
			return new JsonResponse($tags);
		} else {
			return new JsonResponse([]);
		}
	}

	private function assertValidFotoPath($dir, $foto)
	{
		if (!preg_match('/\.(JPE?G|PNG|jpe?g|png)/', $foto)) {
			throw $this->createNotFoundException();
		}
		if (
			!PathUtil::path_valid(PHOTOALBUM_PATH, PathUtil::join_paths($dir, $foto))
		) {
			throw $this->createNotFoundException();
		}
	}

	/**
	 * @param Request $request
	 * @param string $dir
	 * @param string $foto
	 * @return BinaryFileResponse
	 * @Route("/fotoalbum/{dir}/_resized/{foto}", methods={"GET"}, requirements={"dir": ".+", "foto": "[^/]+"})
	 * @Auth({P_ALBUM_READ,P_ALBUM_PUBLIC_READ})
	 */
	public function raw_image_resized(Request $request, string $dir, string $foto)
	{
		$this->assertValidFotoPath($dir, $foto);

		$image = new Foto($foto, new FotoAlbum($dir), true);

		$this->denyAccessUnlessGranted(
			FotoAlbumVoter::BEKIJKEN,
			$image->getAlbum()
		);

		if (!$image->exists()) {
			throw $this->createNotFoundException();
		} elseif (
			!is_file($image->getResizedPath()) ||
			!is_readable($image->getResizedPath())
		) {
			$image->createResized();
		}

		$response = new BinaryFileResponse(
			$image->getResizedPath(),
			200,
			[],
			true,
			null,
			true
		);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_INLINE,
			$image->filename
		);
		$response->setExpires(date_create_immutable('+1 day'));
		$response->isNotModified($request);

		return $response;
	}

	/**
	 * @param Request $request
	 * @param string $dir
	 * @param string $foto
	 * @return BinaryFileResponse
	 * @Route("/fotoalbum/{dir}/_thumbs/{foto}", methods={"GET"}, requirements={"dir": ".+", "foto": "[^/]+"})
	 * @Auth({P_ALBUM_READ,P_ALBUM_PUBLIC_READ})
	 */
	public function raw_image_thumb(Request $request, string $dir, string $foto)
	{
		$this->assertValidFotoPath($dir, $foto);

		$image = new Foto($foto, new FotoAlbum($dir), true);

		$this->denyAccessUnlessGranted(
			FotoAlbumVoter::BEKIJKEN,
			$image->getAlbum()
		);

		if (!$image->exists()) {
			throw $this->createNotFoundException();
		} elseif (
			!is_file($image->getThumbPath()) ||
			!is_readable($image->getThumbPath())
		) {
			$image->createThumb();
		}

		$response = new BinaryFileResponse(
			$image->getThumbPath(),
			200,
			[],
			true,
			null,
			true
		);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_INLINE,
			$image->filename
		);
		$response->setExpires(date_create_immutable('+1 day'));
		$response->isNotModified($request);

		return $response;
	}

	/**
	 * @param Request $request
	 * @param string $dir
	 * @param string $foto
	 * @return BinaryFileResponse
	 * @Route("/fotoalbum/{dir}/{foto}", methods={"GET"}, requirements={"dir": ".+", "foto": "[^/]+\.(JPE?G|PNG|jpe?g|png)"})
	 * @Auth({P_ALBUM_READ,P_ALBUM_PUBLIC_READ})
	 */
	public function raw_image(Request $request, string $dir, string $foto)
	{
		$this->assertValidFotoPath($dir, $foto);

		$image = new Foto($foto, new FotoAlbum($dir), true);

		$this->denyAccessUnlessGranted(
			FotoAlbumVoter::BEKIJKEN,
			$image->getAlbum()
		);

		if (!$image->exists()) {
			throw $this->createNotFoundException();
		}

		$response = new BinaryFileResponse(
			$image->getFullPath(),
			200,
			[],
			true,
			null,
			true
		);
		$response->setContentDisposition(
			$request->query->has('download')
				? ResponseHeaderBag::DISPOSITION_ATTACHMENT
				: ResponseHeaderBag::DISPOSITION_INLINE,
			$image->filename
		);
		$response->setExpires(date_create_immutable('+1 day'));
		$response->isNotModified($request);

		return $response;
	}

	/**
	 * @param $dir
	 * @return Response
	 * @Route("/fotoalbum/{dir}", methods={"GET"}, requirements={"dir": ".+"}, defaults={"dir": ""})
	 * @Auth({P_ALBUM_READ,P_ALBUM_PUBLIC_READ})
	 */
	public function bekijken($dir)
	{
		if ($dir == '' && !$this->mag(P_ALBUM_READ)) {
			$dir = 'Publiek';
		}

		$album = $this->fotoAlbumRepository->getFotoAlbum($dir);

		$this->denyAccessUnlessGranted(FotoAlbumVoter::BEKIJKEN, $album);

		if ($album->dirname === 'Posters') {
			$album->orderByDateModified();
		}
		return $this->render('fotoalbum/album.html.twig', [
			'album' => $album,
			'breadcrumbs' => FotoAlbumBreadcrumbs::getBreadcrumbs($album),
		]);
	}
}
