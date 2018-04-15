<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsResource;
use App\News;
use Illuminate\Http\Request;
use Validator;
class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //
        $news = News::paginate(10);

        return NewsResource::collection($news);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate news data
        Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'title' => 'required|string|max:250|min:5',
            'description' => 'required|string|max:490',
            'text' => 'required|string'
        ])->validate();

        $news = new News();

        $news->date = $request->date;
        $news->title = e(trim($request->title));
        $news->description = e(trim($request->description));
        $news->text = e(trim($request->text));

        if($news->save()){
            $output = ['success' => true , 'id' => $news->id];
            $status = $this->status_success;
        }else{
            $output = ['success' => false , 'error' => "cannot create news entity now , try again later"];
            $status = $this->status_server_error;
        }

        return response()->json($output,$status);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        Validator::make(
            ['id' => $id],
            [
                'id' => 'required|integer|exists:news,id',

            ]
        )->validate();

        $news = News::find($id);

        return new NewsResource($news);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // get the new news data and assign the id to be validated
        $data = $request->all();
        $data['id'] = $id;
        //validate news data
        Validator::make(
            $data,
                [
                    'id' => 'required|integer|exists:news,id',
                    'date' => 'required|date_format:Y-m-d',
                    'title' => 'required|string|max:250|min:5',
                    'description' => 'required|string|max:490',
                    'text' => 'required|string'
                ]
            )->validate();

        $news = News::find($id);

        $news->date = $request->date;
        $news->title = e(trim($request->title));
        $news->description = e(trim($request->description));
        $news->text = $request->text;

        if($news->save()){
            $output = ['success' => true ];
            $status = $this->status_success;
        }else{
            $output = ['success' => false , 'error' => "cannot update news entity now , try again later"];
            $status = $this->status_server_error;
        }

        return response()->json($output,$status);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // validate news id exists

        Validator::make(
            ['id' => $id],
            [
                'id' => 'required|integer|exists:news,id',
            ]
        )->validate();

        $news = News::find($id)->delete();

        if($news->trashed()){
            $output = ['success' => true , 'result' => 'news entity deleted'];
            $status = $this->status_success;
        }else{
            $output = ['success' => false , 'error' => "cannot delete news entity now , try again later"];
            $status = $this->status_server_error;
        }

        return response()->json($output,$status);
    }
}
