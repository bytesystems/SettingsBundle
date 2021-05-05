<?php

namespace Bytesystems\SettingsBundle\Twig;

use Bytesystems\SettingsBundle\SettingManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension for retrieving settings in Twig templates.
 *
 * @author Dmitriy Scherbina <http://dmishh.com>
 */
class SettingsExtension extends AbstractExtension
{
    private $settingsManager;

    public function __construct(SettingManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_setting', [$this->settingsManager, 'get']),
            new TwigFunction('get_settings', [$this->settingsManager, 'all']),
            new TwigFunction('get_setting_config', [$this->settingsManager, 'config']),
        ];
    }
}
