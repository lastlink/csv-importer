<?php
/**
 * Categories.php
 * Copyright (c) 2019 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III CSV Importer.
 *
 * Firefly III CSV Importer is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III CSV Importer.If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace App\Services\CSV\Mapper;

use App\Services\FireflyIIIApi\Model\Category;
use App\Services\FireflyIIIApi\Request\GetCategoriesRequest;

/**
 * Class Categories
 */
class Categories implements MapperInterface
{

    /**
     * Get map of objects.
     *
     * @return array
     */
    public function getMap(): array
    {
        $result   = [];
        $request  = new GetCategoriesRequest;
        $response = $request->get();
        /** @var Category $category */
        foreach ($response as $category) {
            $result[$category->id] = sprintf('%s', $category->name);
        }

        return $result;
    }
}
