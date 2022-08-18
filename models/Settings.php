<?php namespace Winter\SEO\Models;

use Config;
use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var array Behaviors implemented by this model.
     */
    public $implement = [\System\Behaviors\SettingsModel::class];

    /**
     * @var string Unique code
     */
    public $settingsCode = 'winter_seo_settings';

    /**
     * @var mixed Settings form field definitions
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * Initialize the seed data for this model. This only executes when the
     * model is first created or reset to default.
     */
    public function initSettingsData(): void
    {
        $contentsFromConfig = function ($key) {
            $default = '';
            $path = Config::get("winter.seo::{$key}.path");
            if (!empty($path) && file_exists($path)) {
                $default = file_get_contents($path);
            }

            return Config::get("winter.seo::{$key}.contents", $default);
        };

        $this->meta_tags = Config::get('winter.seo::meta_tags', []);
        $this->link_tags = Config::get('winter.seo::link_tags', []);
        $this->humans_txt = $contentsFromConfig('humans_txt');
        $this->robots_txt = $contentsFromConfig('robots_txt');
        $this->security_txt = $contentsFromConfig('security_txt');
    }
}
