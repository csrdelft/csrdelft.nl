<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;

/**
 * Twitter widget
 *
 * @param int optional $arguments['lines']
 * @param int optional $arguments['width'] Breedte
 * @param int optional $arguments['height'] Hoogte
 *
 * @since 27/03/2019
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @example [twitter][/twitter]
 */
class BbTwitter extends BbTag
{

    /**
     * @var string
     */
    public $url;

    public static function getTagName()
    {
        return 'twitter';
    }

    public function renderLight()
    {
        return BbHelper::lightLinkBlock(
            'twitter',
            'https://twitter.com/' . $this->url,
            'Twitter',
            'Tweets van @' . $this->url
        );
    }

    public function render()
    {
        // widget size
        $width = 580;
        $height = 300;


        $script = <<<HTML
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
HTML;

        if (preg_match('/status/', $this->url)) {
            return <<<HTML
<blockquote class="twitter-tweet" data-lang="nl" data-dnt="true" data-link-color="#0a338d">
	<a href="{$this->url}">Tweet op Twitter</a>
</blockquote>
{$script}
HTML;
        }

        return <<<HTML
<a class="twitter-timeline"
	 data-lang="nl"
	 data-width="{$width}"
	 data-height="{$height}"
	 data-dnt="true"
	 data-theme="light"
	 data-link-color="#0a338d"
	 href="https://twitter.com/{$this->url}">
	 	Tweets van {$this->url}
</a>
{$script}
HTML;

    }

    /**
     * @param array $arguments
     * @throws BbException
     */
    public function parse($arguments = [])
    {
        $this->url = $this->readMainArgument($arguments);
        if (str_starts_with($this->url, '@')) {
            $this->url = 'https://twitter.com/' . $this->url;
        }
        if (!preg_match('^https?://(www.)?twitter.com/', $this->url)) {
            throw new BbException("Not a valid twitter url");
        }
    }
}
