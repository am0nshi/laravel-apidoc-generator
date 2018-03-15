<?php

namespace Mpociot\ApiDoc\Swagger;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;

class CollectionWriter
{
    /**
     * @var Collection
     */
    private $routeGroups;
    private $bindings;

    /**
     * CollectionWriter constructor.
     *
     * @param Collection $routeGroups
     */
    public function __construct(Collection $routeGroups, $bindings)
    {
//        dd($routeGroups);
        $this->routeGroups = $routeGroups;
        $this->bindings = $bindings;
    }

    public function getCollection()
    {
        $collection = [
            'swagger' => '2.0',
            'info' => [
                'title' => 'some',
                'description' => 'some',
                'version' => 'v2',
            ],
            'host' => env('APP_URL', 'app.fixd.io'),
            'basePath' => '/v1',
            'consumes' => [
                'application/json'
            ],
            'produces' => [
                'application/json'
            ],

            'paths' => $this->routeGroups->mapWithKeys(function ($routes, $groupName) {
                return $routes->mapWithKeys(function ($route) use ($groupName) {
                    $consumes = [];
                    return [
                        route($route['route'], $this->bindings) => [
                            strtolower($route['methods'][0]) => [
                                'tags' => [$groupName],
                                'summary' => $route['title'] != '' ? $route['title'] : url($route['uri']),
                                'description' => $route['description'] ?: "some",
                                'parameters' => collect($route['parameters'])->map(function ($parameter, $key) use (&$consumes) {
                                    $data = [
                                        'name' => $key,
                                        'in' => $key == 'file' ? 'formData' : 'query',
                                        'description' => $parameter['description'] ? implode('; ', $parameter['description']) : ' ',
                                        'required' => $parameter['required'],
                                        'type' => $parameter['type'],
                                    ];
                                    if ($key == 'file') {
                                        $consumes = ['multipart/form-data'];
                                    }
                                    if ($parameter['type'] == 'array') {
                                        $data['items'] = ['some' => 'field'];
                                    }

                                    return $data;
                                })->values()->toArray(),
                                'responses' => (object)[200 => ["description" => $route['response']]],
                                'consumes' => $consumes,
                                'produces' => ['application/json'],
                            ]
                        ]
                    ];
                })->toArray();
            })->toArray(),
        ];

        return json_encode($collection, JSON_PRETTY_PRINT);
    }

    protected function convertUrl($url)
    {

    }
}
