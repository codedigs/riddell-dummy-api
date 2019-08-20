<?php

if (!function_exists("transformer"))
{
    /**
     * Use league/fractal library
     *
     * @param  Illuminate\Database\Eloquent\(Collection|Model) $resource
     * @param  League\Fractal\TransformerAbstract $transformer
     * @param  array|string $parse_includes
     * @param  array|string $parse_excludes
     * @return League\Fractal\Scope
     */
    function transformer($resource, $transformer, $parse_includes="", $parse_excludes="")
    {
        $fractal = new \League\Fractal\Manager;
        $fractal->parseIncludes($parse_includes);
        $fractal->parseExcludes($parse_excludes);
        $resourceClass = "League\\Fractal\\Resource\\";
        $resourceClass .= ($resource instanceof Illuminate\Database\Eloquent\Collection)
                            ? "Collection" : "Item";
        return $fractal->createData(new $resourceClass($resource, $transformer));
    }
}