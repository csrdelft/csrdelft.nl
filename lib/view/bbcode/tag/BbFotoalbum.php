<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\Security\Voter\Entity\FotoAlbumVoter;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\HostUtil;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\entity\fotoalbum\FotoTagAlbum;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\fotoalbum\FotoAlbumBBView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * Fotoalbum
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * Albumweergave (default):
 * @param boolean optional $arguments['compact'] Compacte weergave
 * @param integer optional $arguments['rows'] Aantal rijen
 * @param integer optional $arguments['perrow'] Aantal kolommen
 * @param boolean optional $arguments['bigfirst'] Eerste foto groot
 * @param string optional $arguments['big'] Indexen van foto's die groot moeten, of patroon 'a', 'b' of 'c'
 *
 * @example [fotoalbum compact bigfirst]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum rows=2 perrow=5 big=a]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum big=0,5,14]/pad/naar/album[/fotoalbum]
 *
 * Sliderweergave:
 * @param boolean optional $arguments['slider'] Slider weergave
 * @param integer optional $arguments['interval'] Slider interval in seconden
 * @param boolean optional $arguments['random'] Slider met random volgorde
 * @param boolean optional $arguments['height'] Slider hoogte in pixels
 *
 * @example [fotoalbum slider interval=10 random height=200]/pad/naar/album[/fotoalbum]
 * @example [fotoalbum]laatste[/fotoalbum]
 */
class BbFotoalbum extends BbTag
{
	/**
	 * @var array
	 */
	private $arguments;
	/**
	 * @var bool|FotoAlbum|FotoTagAlbum|null
	 */
	private $album;
	/**
	 * @var string
	 */
	private $albumUrl;

	public function __construct(
		private readonly FotoAlbumRepository $fotoAlbumRepository,
		private readonly Security $security,
		private readonly Environment $twig
	) {
	}

	public static function getTagName()
	{
		return 'fotoalbum';
	}
	public function isAllowed()
	{
		return ($this->album != null &&
			$this->security->isGranted(FotoAlbumVoter::BEKIJKEN, $this->album)) ||
			($this->album == null && $this->security->isGranted('ROLE_LOGGED_IN'));
	}

	public function renderPreview()
	{
		return ' 📷 ';
	}

	public function renderLight()
	{
		$album = $this->album;
		$beschrijving = count($album->getFotos()) . ' foto\'s';
		$cover = HostUtil::getCsrRoot() . $album->getCoverUrl();
		return BbHelper::lightLinkBlock(
			'fotoalbum',
			$album->getUrl(),
			$album->dirname,
			$beschrijving,
			$cover
		);
	}

	public function render()
	{
		$album = $this->album;
		$arguments = $this->arguments;
		if (isset($arguments['slider'])) {
			return $this->twig->render('fotoalbum/slider.html.twig', [
				'fotos' => ArrayUtil::array_shuffle($album->getFotos()),
			]);
		} else {
			$view = new FotoAlbumBBView($album);

			if ($this->env->quote_level > 0 || isset($arguments['compact'])) {
				$view->makeCompact();
			}
			if (isset($arguments['rows'])) {
				$view->setRows((int) $arguments['rows']);
			}
			if (isset($arguments['perrow'])) {
				$view->setPerRow((int) $arguments['perrow']);
			}
			if (isset($arguments['bigfirst'])) {
				$view->setBig(0);
			}
			if (isset($arguments['big'])) {
				if ($arguments['big'] == 'first') {
					$view->setBig(0);
				} else {
					$view->setBig($arguments['big']);
				}
			}
		}
		return $view->getHtml();
	}

	/**
	 * @param string $url
	 * @return bool|FotoAlbum|FotoTagAlbum|null
	 * @throws BbException
	 */
	private function getAlbum(string $url)
	{
		try {
			if ($url === 'laatste') {
				$album = $this->fotoAlbumRepository->getMostRecentFotoAlbum();
			} else {
				//vervang url met pad
				$url = str_ireplace(HostUtil::getCsrRoot(), '', $url);
				//check fotoalbum in url
				$url = str_ireplace('fotoalbum/', '', $url);
				//check slash voor pad
				if (str_starts_with($url, '/')) {
					$url = substr($url, 1);
				}
				$album = $this->fotoAlbumRepository->getFotoAlbum($url);
			}
			return $album;
		} catch (NotFoundHttpException) {
			return null;
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->albumUrl = $this->readMainArgument($arguments);
		$this->arguments = $arguments;
		$this->album = $this->getAlbum($this->albumUrl);
		if ($this->album == null) {
			throw new BbException(
				'<div class="bb-block">Fotoalbum niet gevonden: ' .
					htmlspecialchars($this->albumUrl) .
					'</div>'
			);
		}
	}
}
