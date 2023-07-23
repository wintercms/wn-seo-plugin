<?php

namespace Winter\SEO\Tests\Unit;

use Closure;
use Config;
use System\Tests\Bootstrap\TestCase;
use Winter\SEO\Behaviors\SeoableModel;
use Winter\SEO\Tests\Fixtures\BaseSeoableModel;
use Winter\Storm\Database\Attach\File;
use Winter\Storm\Database\Model;
use Winter\Storm\Exception\SystemException;
use Winter\Storm\Support\Str;

class SeoableBehaviorTest extends TestCase
{
    /**
     * Configure the provided model instance
     */
    protected function prepareModel(BaseSeoableModel $model, array $behaviorConfig = [], array $attributes = []): Model
    {
        foreach ($behaviorConfig as $key => $value) {
            $model->addDynamicProperty($key, $value);
        }

        foreach ($attributes as $key => &$value) {
            if (is_array($value)) {
                $model->addJsonable($key);
                $value = json_encode($value);
            }
        }

        $model->addJsonable($model->seoableDataColumn ?: 'metadata');

        $model->extendClassWith(SeoableModel::class);

        $model->attributes = $attributes;

        return $model;
    }

    /**
     * Run a test on a model instance
     */
    protected function testModel(array|Closure $expected, array|BaseSeoableModel $behaviorConfig = [], array $attributes = []): void
    {
        if (is_array($behaviorConfig)) {
            $model = $this->prepareModel(new BaseSeoableModel(), $behaviorConfig, $attributes);
        } else {
            $model = $behaviorConfig;
        }

        if ($expected instanceof Closure) {
            $this->assertTrue($expected($model));
        } else {
            // Ensure that the expected array has both 'meta' and 'link' keys
            $types = ['meta', 'link'];
            foreach ($types as $type) {
                if (!isset($expected[$type])) {
                    $expected[$type] = [];
                }
            }
            $this->assertEquals($expected, $model->seoableGetData());
        }
    }


    public function testDataColumn()
    {
        // The default column name is 'metadata'
        $this->testModel(function (BaseSeoableModel $model) {
            return $model->seoableDataColumn === 'metadata';
        });

        // The column name can be changed
        $newColumn = 'additional_data';
        $seoData = ['meta' => ['og:title' => $newColumn]];
        $this->testModel(
            $seoData,
            ['seoableDataColumn' => $newColumn, 'seoableMetaFrom' => []],
            [$newColumn => ['seo_data' => $seoData]]
        );
    }

    /**
     * The order of priority for resolving the value is:
     * attribute, relation, method, and finally as a static string
     */
    public function testResolveValuePriority()
    {
        // Initialize variables
        $source = 'sourceName';
        $config = [
            'seoableMetaFrom' => ['og:title' => $source],
            'seoableLinkFrom' => ['canonical' => $source],
        ];
        $expect = function ($value) {
            return [
                'meta' => ['og:title' => $value],
                'link' => ['canonical' => $value],
            ];
        };

        // Setup a model with an attribute, relation, and method
        $model = $this->prepareModel(
            new class extends BaseSeoableModel {
                public $belongsTo = [
                    'sourceName' => [BaseSeoableModel::class],
                ];
                public function sourceName()
                {
                    $source = Str::after(__METHOD__, '::');
                    if (!isset($this->attributes[$source]) && $this->hasRelation($source)) {
                        return $this->handleRelation($source);
                    }
                    return 'methodValue';
                }
            },
            $config,
            [$source => 'attrValue', 'source_name_id' => 'relationValue']
        );
        $model->setRelation($source, new BaseSeoableModel);

        // Test that the attribute value is used
        $this->testModel($expect('attrValue'), $model);

        // Test that the relation is loaded from when the attribute doesn't exist
        unset($model->attributes[$source]);
        $this->testModel($expect('relationValue'), $model);

        // Test that the method is used when the attribute and relation don't exist
        unset($model->belongsTo[$source]);
        $this->testModel($expect('methodValue'), $model);

        // Test that the static value is used when the attribute, relation, and method don't exist
        $this->testModel($expect($source), $config);
    }

    /**
     * No exceptions are thrown for an invalid value when debug mode is disabled
     */
    public function testInvalidResolveValueNoException()
    {
        $currentDebug = Config::get('app.debug', false);
        Config::set('app.debug', false);
        $this->testModel(
            ['meta' => ['og:title' => null]],
            ['seoableMetaFrom' => ['og:title' => 'sourceName']],
            ['sourceName' => new \stdClass()]
        );
        Config::set('app.debug', $currentDebug);
    }

    /**
     * A SystemException is thrown for invalid values when debug mode is enabled
     */
    public function testInvalidResolveValueException()
    {
        $currentDebug = Config::get('app.debug', false);
        Config::set('app.debug', true);
        $this->expectException(SystemException::class);
        $this->testModel(
            ['meta' => ['og:title' => null]],
            ['seoableMetaFrom' => ['og:title' => 'sourceName']],
            ['sourceName' => new \stdClass()]
        );
        Config::set('app.debug', $currentDebug);
    }

    /**
     * Array values return the first element
     */
    public function testResolveArrayValue()
    {
        $this->testModel(
            ['meta' => ['og:title' => 'first']],
            ['seoableMetaFrom' => ['og:title' => 'sourceName']],
            ['sourceName' => ['first', 'second']]
        );
    }

    /**
     * Collections return the first element
     */
    public function testResolveCollectionValue()
    {
        $this->testModel(
            ['meta' => ['og:title' => 'first']],
            ['seoableMetaFrom' => ['og:title' => 'sourceName']],
            ['sourceName' => collect(['first', 'second'])]
        );
    }

    /**
     * File models return the public URL
     */
    public function testResolveFileValue()
    {
        $file = new File;
        $file->is_public = true;
        $file->fromData('test', 'test.txt');

        $this->testModel(
            ['meta' => ['og:image' => $file->getPath()]],
            ['seoableMetaFrom' => ['og:image' => 'sourceName']],
            ['sourceName' => $file]
        );
    }

    public function testPrioritizeStoredValues()
    {
        // Values set in the model's seoableDataColumn always take precedence
        $this->testModel(
            ['meta' => ['og:title' => 'fromSeoData']],
            ['seoableMetaFrom' => ['og:title' => 'attrName']],
            [
                'attrName' => 'attrValue',
                'metadata' => [
                    'seo_data' => [
                        'meta' => [
                            'og:title' => 'fromSeoData'
                        ],
                    ],
                ],
            ]
        );

        // Values not set in the seoable$TypeFrom config but present in the model's
        // seoableDataColumn are still present in the output
        $this->testModel(
            ['meta' => ['og:title' => 'fromSeoData']],
            [],
            [
                'attrName' => 'attrValue',
                'metadata' => [
                    'seo_data' => [
                        'meta' => [
                            'og:title' => 'fromSeoData'
                        ],
                    ],
                ],
            ]
        );

        // Resolvable (non-static) values set in the seoable$TypeFrom config are
        // present in the output when they have a value
        $this->testModel(
            ['meta' => ['og:title' => 'attrValue']],
            ['seoableMetaFrom' => ['og:title' => 'attrName']],
            [
                'attrName' => 'attrValue',
            ]
        );

        // Resolvable (non-static) values set in the seoable$TypeFrom config are
        // not present in the output when they don't have a value
        $this->testModel(
            ['meta' => ['og:title' => null]],
            ['seoableMetaFrom' => ['og:title' => 'attrName']],
            [
                'attrName' => null,
            ]
        );

        // Non-resolvable (static) values set in the seoable$TypeFrom config are
        // present in the output
        $this->testModel(
            ['meta' => ['og:type' => 'article']],
            ['seoableMetaFrom' => ['og:type' => 'article']],
            []
        );
    }
}
