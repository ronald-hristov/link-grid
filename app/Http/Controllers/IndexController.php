<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IndexController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getAllAction(Request $request)
    {
        $links = DB::table('links')
            ->join('colors', 'links.color_id', '=', 'colors.id')
            ->select('links.id', 'links.title', 'colors.hex as color', 'colors.name as colorName', 'links.url as link')
            ->orderBy('links.id')
            ->get();

        $colors = DB::table('colors')->get();


        return response()->json(['links' => $links, 'colors' => $colors]);
    }

    public function postAction(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'link' => 'required',
            'colorName' => 'required',
        ]);

        $post = $request->post();
        $colorId = DB::table('colors')->where('name', $post['colorName'])->select('id')->first();


        if (!$colorId) {
            throw new \RuntimeException('Non existing color provided is not valid');
        }

        $colorId = $colorId->id;
        $values = [
            'id' => $post['id'],
            'color_id' => $colorId,
            'url' => $post['link'],
            'title' => $post['title'],
        ];

        $isUpdate = DB::table('links')->where('id', $post['id'])->exists();

        if ($isUpdate) {
            //update
            DB::table('links')->where('id', $post['id'])->update($values);
        } else {
            // insert
            DB::table('links')->insert($values);
        }

        $response = response()->json();
        if (!$isUpdate) {
            $response->setStatusCode(201);
        }

        return $response;
    }

    public function deleteAction(Request $request, $id)
    {
        if (DB::table('links')->where('id', $id)->exists()) {
            DB::table('links')->delete($id);
        }

        return response()->json();
    }

}
