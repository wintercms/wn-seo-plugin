<?php

namespace Winter\SEO\Behaviors;

use Config;
use Illuminate\Support\Collection;
use Log;
use Winter\Storm\Database\Attach\File;
use Winter\Storm\Database\ModelBehavior;
use Winter\Storm\Exception\SystemException;

/**
 * Seoable model extension
 *
 * Usage:
 *
 * In the model class definition:
 *
 *   public $implement = ['@Winter.SEO.Behaviors.SeoableModels'];
 *
 *   /**
 *    * @var string The name of the jsonable column that the SEO metadata should be stored under. Defaults to 'metadata', data will be stored under the 'seo' key within the column
 *    * /
 *   public $seoableDataColumn = 'metadata';
 *
 *   /**
 *    * @var array The mapping of common SEO Meta fields to the model's attributes, relations, methods, or static values ['meta_name' => 'attribute'].
 *    *
 *    * This mapping will only be used when the relevent SEO Meta fields are not present in the seoableDataColumn
 *    *
 *    * By default each attribute will be looked for as a model attribute (anything that would return true from $model->hasAttribute(),
 *    * then as a relation (using $model->getRelationValue() / $relation->getSimpleValue(), and if the relation returns multiple values
 *    * then only the first value is used), then as a method, and finally if the value provided cannot be resolved, it will be treated
 *    * as a static value and used as-is.
 *    *
 *    * "og:image" additionally supports one of the following:
 *    * - any input that \System\Classes\ImageResizer::filterGetUrl() can handle (eg. a File model or AttachOne relationship,
 *    * an absolute url, or a relative URL (asset from media, module, plugin, or theme))
 *    * - an attachMany relationship, in which case the first image will be used
 *    * /
 *   public $seoableMetaFrom = [
 *       'og:title' => 'name',
 *       'og:description' => 'description',
 *       'og:image' => 'featured_images',
 *       'og:type' => 'article',
 *   ];
 *
 *   /**
 *    * @var array The mapping of common SEO Link fields to the model's attributes or methods ['link_rel' => 'attribute'].
 *    * This mapping will only be used when the relevent SEO Link fields are not present in the seoableDataColumn
 *    * By default each attribute will be looked for as a model property, then as a method
 *    * If the value provided cannot be resolved, it will be treated as a static value and used as-is
 *    * /
 *   public $seoableLinkFrom = [
 *       'robots' => 'nofollow',
 *   ];
 *
 *   /**
 *    * @var boolean Manually control if the activities field gets automatically injected into backend forms
 *    * for this model (default from the winter.seo.autoInjectSeoFields config setting)
 *    * /
 *   public $seoableInjectSeoFields = true;
 */
class SeoableModel extends ModelBehavior
{
    public function __construct($model)
    {
        parent::__construct($model);

        // Load the model property that was initialized by the parent behavior
        $model = $this->model;

        // Setup the default values for the behaviour
        $this->setupDefaults();
        $this->validateConfiguration();
    }

    /**
     * Setup the default values for the behaviour
     */
    protected function setupDefaults(): void
    {
        if (!$this->model->propertyExists('seoableDataColumn')) {
            $this->model->addDynamicProperty('seoableDataColumn', 'metadata');
        }

        if (!$this->model->propertyExists('seoableMetaFrom')) {
            $this->model->addDynamicProperty('seoableMetaFrom', []);
        }

        if (!$this->model->propertyExists('seoableLinkFrom')) {
            $this->model->addDynamicProperty('seoableLinkFrom', []);
        }

        if (!$this->model->propertyExists('seoableInjectSeoFields')) {
            $this->model->addDynamicProperty('seoableInjectSeoFields', Config::get('winter.seo::autoInjectSeoFields', true));
        }
    }

    /**
     * Validate the configuration of the behaviour
     */
    protected function validateConfiguration(): void
    {
        // Validate the seoableDataColumn
        if (!in_array($this->model->seoableDataColumn, $this->model->getJsonable())) {
            throw new SystemException(sprintf('The seoableDataColumn "%s" is not defined as jsonable on the model "%s". Please add it to the jsonable property', $this->model->seoableDataColumn, get_class($this->model)));
        }

        // @TODO: Attempt to resolve values defined in seoableMetaFrom and seoableLinkFrom to ensure they can be found on the model
    }

    /**
     * Get the SEO data for the model, can be overridden on the model level.
     * @return array ['meta' => [], 'link' => []]
     */
    public function seoableGetData(): array
    {
        $seoData = $this->model->{$this->model->seoableDataColumn}['seo_data'] ?? [];
        $data = [
            'meta' => [],
            'link' => [],
        ];

        // Iterate over the types of SEO data
        foreach ($data as $type => &$values) {
            // Iterate over the defined mappings for the type
            foreach ($this->model->{'seoable' . ucfirst($type) . 'From'} as $name => $source) {
                // If a source value for the mapping is defined in the SEO data, use it, otherwise attempt to resolve the mapping
                if (isset($seoData[$type][$name])) {
                    $values[$name] = $seoData[$type][$name];
                } else {
                    $values[$name] = $this->model->seoableResolveValue($source);
                }
            }

            // Populate any remaining values from the stored SEO data
            $data[$type] = array_merge($values, $seoData[$type] ?? []);

            // Clean up the reference
            unset($values);
        }

        return $data;
    }

    /**
     * Resolve a value for SEO data from the meta_from or link_from mappings
     */
    public function seoableResolveValue(string $source): ?string
    {
        // Check for an attribute
        if ($this->model->hasAttribute($source)) {
            return $this->prepareValue($this->model->getAttributeValue($source), $source);
        }

        if ($this->model->hasRelation($source)) {
            $fromRelation = $this->model->getRelationValue($source);
        }

        if (!isset($fromRelation) && $this->model->methodExists($source)) {
            $fromMethod = $this->model->{$source}();
        }

        $value = $fromRelation ?? $fromMethod ?? $source;

        return $this->prepareValue($value, $source);
    }

    /**
     * Prepare a value for SEO data
     * @param mixed $value
     * @throws SystemException if the value is invalid and app.debug is true, otherwise just logs the error
     * @return mixed
     */
    protected function prepareValue($value, string $source)
    {
        if (is_array($value)) {
            $value = collect($value);
        }

        if ($value instanceof Collection) {
            $value = $value->first();
        }

        if ($value instanceof File) {
            $value = $value->getPath();
        }

        if ($value === null || !$this->model->seoableValidateValue($value, $source)) {
            return null;
        }

        return $value;
    }

    /**
     * Validate a resolved value for SEO data
     * @param mixed $value
     * @throws SystemException if the value is invalid and app.debug is true, otherwise just logs the error
     * @return boolean
     */
    public function seoableValidateValue($value, $source): bool
    {
        if (!is_string($value)) {
            $message = sprintf(
                "Invalid type of %s to resolve SEO value from %s on %s",
                gettype($value),
                $source,
                get_class($this->model),
            );
            if (Config::get('app.debug', false)) {
                throw new SystemException($message);
            } else {
                Log::error($message);
            }
            return false;
        }
        return true;
    }
}
