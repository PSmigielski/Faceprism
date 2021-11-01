<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;


class PaginationService
{
    static public function paginate(int $page, QueryBuilder $qb, string $arrayName): array | JsonResponse
    {
        try {
            $adapter = new QueryAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(25);
            $pagerfanta->setCurrentPage($page);
            $data = array();
            foreach ($pagerfanta->getCurrentPageResults() as $entity) {
                $data[] = $entity;
            }
            return [
                "page" => $page,
                "totalPages" => $pagerfanta->getNbPages(),
                "count" => $pagerfanta->getNbResults(),
                $arrayName => $data
            ];
        } catch (OutOfRangeCurrentPageException $e) {
            return new JsonResponse(["message" => "Page not found"], 404);
        }
    }
}
