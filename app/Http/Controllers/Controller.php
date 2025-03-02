<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array $data
     * @param  $rejectTransformIds
     * @param int $code
     * @param string $msg
     * @return JsonResponse
     */
    public function sendResponse($data = [], int $code = 200, string $msg = '', string $statusCode= 'success'): JsonResponse
    {
        $msg = !empty($msg) ? $msg : 'OK';
        return response()->json(
            [
                'status'  => $code,
                'status_code'  => $statusCode,
                'message' => $msg,
                'data'    => $data,
            ]
        );
    }

    /**
     * @param  array $data
     * @param  $code
     * @param  $msg
     * @return JsonResponse
     */
    public function sendError(
        $data = [],
        $code = -5,
        $msg = ''
    ): JsonResponse
    {
        $msg = !empty($msg) ? $msg : '';
        return response()->json(
            [
                'status'  => $code,
                'message' => $msg,
                'data'    => $data,
            ],
        );
    }

    public function paginateData(LengthAwarePaginator $paginate, $items): \Illuminate\Http\JsonResponse {
        return $this->sendResponse([
            'current_page' => $paginate->currentPage(),
            'last_page'    => $paginate->lastPage(),
            'per_page'     => $paginate->perPage(),
            'total'        => $paginate->total(),
            'lists'        => isset($items) ? $items : $paginate->items(),
        ], 200);
    }
}
