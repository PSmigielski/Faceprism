<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use ErrorException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;

class PaginationService
{
    static public function paginate(int $page, QueryBuilder $qb, string $arrayName): array
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
            throw new ErrorException("Page not found", 404);
        }
    }
}
