<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    public function index (Request $request) 
    {
        $data = Storage::get('data.csv');
        $data = explode( ",", $data);
        $cnt = count( $data );
        for( $i=0;$i<$cnt;$i++ ) {
            $csv[] = $data[$i];
        }
        $chunk = array_chunk($csv,3);
        foreach ($chunk as $data) {
            $datas[] = $data;
        }
        $datas['param'] = $request->query();
        return view('SearchPortal')->with('datas', $datas);
    }
}
