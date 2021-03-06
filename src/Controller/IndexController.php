<?php

namespace App\Controller;

use App\StandardOutput;
use Exception;
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Middleware(['security'])]
final class IndexController extends BaseController
{
    /**
     * The root endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $output = new StandardOutput();

        try {
            // Circle
            $type = 'circle';
            $radius = 2;
            $circumference = 2 * pi() * $radius; // C = 2.π.r
            $area = pi() * pow($radius, 2); // A = π.r2

            // Triangle

            $output->status = 200;
            $output->data = [
                'index' => [
                    'name' => 'Endpoints List',
                    'description' => 'Shows all available endpoints.',
                    'last_update' => '2022-07-01 20:30:00',
                ],
            ];
        }
        catch (Exception $exception) {
            $this->catchException($exception, $output);
        }
        finally {
            return $this->respond($output);
        }
    }
}
