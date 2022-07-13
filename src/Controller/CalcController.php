<?php

namespace App\Controller;

use App\StandardOutput;
use Exception;
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Middleware(['security'])]
final class CalcController extends BaseController
{
    /**
     * The circle endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function circle(Request $request): JsonResponse
    {
        $status = 400;
        $data = [];
        $output = new StandardOutput();

        try {
            $type = 'circle';
            $radius = 2;
            $circumference = 2 * pi() * $radius; // C = 2.π.r
            $area = pi() * pow($radius, 2); // A = π.r2
            $status = 200;

            $data = [
                'type' => $type,
                'radius' => $radius,
                'surface' => $area,
                'circumference' => $circumference,
            ];
        }
        catch (Exception $exception) {
            $this->catchException($exception, $output);
        }
        finally {
            return $this->respondData($status, $data);
        }
    }

    /**
     * The triangle endpoint
     *
     * @param $a
     * @param $b
     * @param $c
     * @return JsonResponse
     */
    public function triangle($a, $b, $c): JsonResponse
    {
        $status = 400;
        $data = [];
        $output = new StandardOutput();

        try {
            $type = 'triangle';
            $sides = [(float)$a, (float)$b, (float)$c];
            $diameter = array_sum($sides);

            // 1. Calculate the semi-perimeter (s = (a + b + c)/2)
            $s = $diameter / 2;

            // 2. Calculate the are (A =√[s(s-a)(s-b)(s-c)])
            $area = sqrt($s * ($s - $sides[0]) * ($s - $sides[1]) * ($s - $sides[2]));
            $area = round($area, 2);
            $status = 200;

            $data = [
                'type' => $type,
                'a' => $sides[0],
                'b' => $sides[1],
                'c' => $sides[2],
                'surface' => $area,
                'diameter' => $diameter,
            ];
        }
        catch (Exception $exception) {
            $this->catchException($exception, $output);
        }
        finally {
            return $this->respondData($status, $data);
        }
    }
}
