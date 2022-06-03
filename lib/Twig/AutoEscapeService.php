<?php


namespace CsrDelft\Twig;


use Twig\FileExtensionEscapingStrategy;

class AutoEscapeService
{
    const STRATEGY_ICAL = 'ical';
    const STRATEGY_XML = 'xml';
    const STRATEGY_MAIL = 'mail';

    public function guess(string $name)
    {
        if (str_ends_with($name, '.ical.twig')) {
            return self::STRATEGY_ICAL;
        }
        if (str_ends_with($name, '.xml.twig')) {
            return self::STRATEGY_XML;
        }
        if (str_ends_with($name, '.mail.twig')) {
            return self::STRATEGY_MAIL;
        }

        return FileExtensionEscapingStrategy::guess($name);
    }

}
