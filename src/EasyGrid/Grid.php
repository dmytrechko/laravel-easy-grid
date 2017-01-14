<?php namespace EasyGrid\EasyGrid;

use EasyGrid\EasyGrid\Adapters\RequestParams;
use EasyGrid\EasyGrid\Contracts\ParamsAdapterInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Grid
{
    /**
     * @var string $model - class name of booted model
     */
    protected $model;
    
    /**
     * Instance of query builder
     */
    private $queryBuilder;
    
    /**
     * Laravel collection with results
     */
    private $results;
    
    /**
     * @var ParamsAdapterInterface
     */
    private $paramsAdapter;
    private $searchStrategy;
    private $transformer;
    private $meta = [];
    
    /**
     * Grid constructor.
     * @param ParamsAdapterInterface|null $paramsAdapter
     */
    public function __construct(ParamsAdapterInterface $paramsAdapter = null)
    {
        $this->setParamsAdapter(($paramsAdapter ? new $paramsAdapter(config('easygrid')) : new RequestParams(config('easygrid'))));
    }
    
    public function setParamsAdapter(ParamsAdapterInterface $paramsAdapter)
    {
        $this->paramsAdapter = $paramsAdapter;
        
        return $this;
    }
    
    public function setSearchStrategy(callable $searchStrategy)
    {
        $this->searchStrategy = $searchStrategy;
        
        return $this;
    }
    
    public function setTransformer(callable $transformer)
    {
        $this->transformer = $transformer;
        
        return $this;
    }
    
    /**
     * Getter and fluent setter for config
     * Put an array with key => value to set config
     * @param string|array $name
     * @return $this
     */
    public function config($name)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->paramsAdapter->set($key, $value);
            }
            
            return $this;
        }
        
        return $this->paramsAdapter->get($name);
    }
    
    /**
     * Getter and fluent setter for meta
     *
     * @param $name
     * @return $this|mixed
     */
    public function meta($name)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                array_set($this->meta, $key, $value);
            }
            
            return $this;
        }
        
        return array_get($this->meta, $name);
    }
    
    public function getMeta()
    {
        return $this->meta;
    }
    
    /**
     * Boot the model into grid and fetch all necessary details
     * @param $model
     * @return $this
     * @throws GridException
     */
    public function boot($model)
    {
        $relection = new \ReflectionClass($model);
        if (!$relection->isSubclassOf(Model::class)) {
            throw new GridException("{$model} must be subclass of " . Model::class);
        }
        $this->model = $model;
        $this->queryBuilder = new $this->model();
        return $this;
    }
    
    /**
     * Run grid instance to fetch results
     */
    public function run()
    {
        $this
            ->applyFilters()
            ->applySearch()
            ->finalize()
            ->applyTransformer()
            ->getResults();
    }
    
    /**
     * Apply your search strategy
     * @return $this
     */
    protected function applySearch()
    {
        $this->meta(['search' => $this->config('search')]);
        if ($this->searchStrategy) {
            $this->queryBuilder = call_user_func($this->searchStrategy, $this->queryBuilder, $this->config('search'));
        }
        
        return $this;
    }
    
    /**
     * Apply filters
     * @return $this
     */
    protected function applyFilters()
    {
        $filters = array_filter(explode(',', $this->config('filters')));
        $this->meta(['filters' => $filters]);
        foreach ($filters as $filter) {
            list($scope, $params) = $this->performFilter($filter);
            if (!$this->hasScope($filter)) {
                continue;
            }
            $this->queryBuilder = call_user_func_array([$this->queryBuilder, $scope], $params);
        }
        
        return $this;
    }
    
    private function performFilter($filter)
    {
        list($scope, $params) = str_contains($filter, ':') ? explode(':', $filter) : [$filter, ''];
        $params = explode('.', $params);
        
        return [$scope, $params];
    }
    
    /**
     * Apply transformer
     * @return $this
     */
    protected function applyTransformer()
    {
        $this->results->map($this->transformer);
        return $this;
    }
    
    /**
     * Set meta info and fill the results.
     * @return $this
     */
    protected function finalize()
    {
        $this->meta(['total' => ($this->model)::count()])
            ->meta(['count' => $this->queryBuilder->count() ?: $this->meta('total')])
            ->meta(['offset' => $this->config('offset')])
            ->meta(['limit' => $this->config('limit')])
            ->meta(['limit' => $this->config('limit')]);
        
        $this->results = $this->queryBuilder
            ->offset($this->config('offset'))
            ->limit($this->config('limit'))
            ->get();
        
        return $this;
    }
    
    /**
     * Get results
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getResults()
    {
        if ($this->results instanceof Collection) {
            return $this->results;
        }
        
        return collect();
    }
    
    /**
     * Check for scope
     *
     * @param $name
     * @return bool
     */
    protected function hasScope($name)
    {
        return method_exists($this->model, 'scope' . ucfirst($name));
    }
    
}

