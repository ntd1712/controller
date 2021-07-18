<?php

namespace Chaos\Controller;

use Chaos\Support\Resolver\FilterResolver;
use Chaos\Support\Resolver\OrderResolver;
use Chaos\Support\Resolver\PagerResolver;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use function Chaos\serializer;

/**
 * Trait LaravelControllerTrait.
 *
 * A controller can call multiple services.
 *
 * <code>
 * public function __construct(
 *   LookupService $lookupService,
 *   DashboardService $dashboardService,
 *   DashboardRepository $dashboardRepository
 * ) {
 *   parent::__invoke(
 *     $this->service = $lookupService,
 *     $this->dashboardService = $dashboardService,
 *     $this->dashboardRepository = $dashboardRepository
 *   );
 * }
 * </code>
 *
 * @author t(-.-t) <ntd1712@mail.com>
 *
 * @property \Illuminate\Foundation\Application $container The container instance.
 * @property \Chaos\Service\EntityRepositoryServiceInterface $service The service instance.
 */
trait LaravelControllerTrait
{
    /**
     * Displays a listing of the resource.
     * This is the default `index` action, you can override this in the derived class.
     *
     * GET /api/v1/resource
     *
     * @param Request $request The request.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array|\Illuminate\Http\Response
     */
    public function indexAction(Request $request)
    {
        $input = $request->input();
        $permit = $this->service->repository->fieldMappings;
        $criteria = [];

        FilterResolver::make()->setPermit($permit)->resolve($input, $criteria);
        OrderResolver::make()->setPermit($permit)->resolve($input, $criteria);

        if (false !== PagerResolver::make()->resolve($input, $criteria)) {
            $result = $this->service->paginate($criteria);
            $result['options'] = ['path' => Paginator::resolveCurrentPath()];

            /* @var LengthAwarePaginator $paginator */
            $paginator = $this->container->makeWith(LengthAwarePaginator::class, $result);
            $meta = $paginator->appends($_REQUEST)->toArray();
            unset($meta['data']);
        } else {
            $result = $this->service->search($criteria);
            $meta = [
                'total' => $result['total']
            ];
        }

        return [
            'data' => serializer()->toArray($result['items']),
            'meta' => $meta
        ];
    }

    /**
     * Shows the form for creating a new resource.
     * This is the default `create` action, you can override this in the derived class.
     *
     * GET /api/v1/resource/create
     *
     * @return array|\Illuminate\Http\Response
     */
    public function createAction()
    {
        return ['XXX'];
    }

    /**
     * Stores a newly created resource in storage.
     * This is the default `store` action, you can override this in the derived class.
     *
     * POST /api/v1/resource
     *
     * @param Request $request The request.
     *
     * @return array|\Illuminate\Http\Response
     */
    public function storeAction(Request $request)
    {
        $result = $this->service->create($request->all());

        return [
            'data' => serializer()->toArray($result)
        ];
    }

    /**
     * Displays the specified resource.
     * This is the default `show` action, you can override this in the derived class.
     *
     * GET /api/v1/resource/:id
     *
     * @param mixed $id The route parameter ID.
     *
     * @return array|\Illuminate\Http\Response
     */
    public function showAction($id)
    {
        $result = $this->service->read($id);

        return [
            'data' => serializer()->toArray($result)
        ];
    }

    /**
     * Shows the form for editing the specified resource.
     * This is the default `edit` action, you can override this in the derived class.
     *
     * GET /api/v1/resource/:id/edit
     *
     * @param mixed $id The route parameter ID.
     *
     * @return array|\Illuminate\Http\Response
     */
    public function editAction($id)
    {
        return ["XXX: {$id}"];
    }

    /**
     * Updates the specified resource in storage.
     * This is the default `update` action, you can override this in the derived class.
     *
     * PUT/PATCH /api/v1/resource/:id
     *
     * @param Request $request The request.
     * @param mixed $id The route parameter ID.
     *
     * @return array|\Illuminate\Http\Response
     */
    public function updateAction(Request $request, $id)
    {
        $result = $this->service->update($id, $request->all());

        return [
            'data' => serializer()->toArray($result)
        ];
    }

    /**
     * Removes the specified resource(s) from storage.
     * This is the default `destroy` action, you can override this in the derived class.
     *
     * DELETE /api/v1/resource/:id [,:id2,:id3,.. ]
     *
     * @param mixed $id The route parameter ID.
     *
     * @return array|\Illuminate\Http\Response
     */
    public function destroyAction($id)
    {
        if (false !== strpos($id, ',')) {
            $id = array_fill_keys($this->service->repository->identifier, explode(',', $id));
        }

        $result = $this->service->delete($id);

        return [
            'data' => serializer()->toArray($result)
        ];
    }
}
