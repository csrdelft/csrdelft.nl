<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Sentry\SentryBundle\SentryBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use League\Bundle\OAuth2ServerBundle\LeagueOAuth2ServerBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;

return [
	FrameworkBundle::class => ['all' => true],
	TwigBundle::class => ['all' => true],
	WebProfilerBundle::class => ['dev' => true, 'test' => true],
	DoctrineBundle::class => ['all' => true],
	DoctrineMigrationsBundle::class => ['all' => true],
	MonologBundle::class => ['all' => true],
	DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
	MakerBundle::class => ['dev' => true],
	SentryBundle::class => ['all' => true],
	SecurityBundle::class => ['all' => true],
	TwigExtraBundle::class => ['all' => true],
	LeagueOAuth2ServerBundle::class => ['all' => true],
	NelmioCorsBundle::class => ['all' => true],
];
