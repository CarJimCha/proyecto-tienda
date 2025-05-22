<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

class PaginadorService
{
    private PaginatorInterface $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function paginar(QueryBuilder $qb, Request $request, int $defaultPerPage = 25)
    {
        $perPage = $request->query->getInt('items_per_page', $defaultPerPage);
        $page = $request->query->getInt('page', 1);

        return $this->paginator->paginate($qb, $page, $perPage);
    }
}
