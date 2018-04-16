<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsResource;
use App\News;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        //
        Validator::make(
            $request->all(), [
                'title' => 'sometimes|string|max:250|min:5',
                'from' => 'sometimes|date_format:Y-m-d',
                'to' => 'sometimes|date_format:Y-m-d',
                'deleted' => 'sometimes|in:1'

        ])->validate();


        $news = new News();

        if(Request()->has('title'))
            $news = $news->where('title' , 'like' , "%" . Request()->input('title') . "%");

        if(Request()->has('from'))
            $news = $news->where('date' , '>=' , Request()->input('from'));

        if(Request()->has('to'))
            $news = $news->where('date' , '<' , Request()->input('to'));

        if(Request()->has('deleted'))
            $news = $news->withTrashed();

        $news = $news->paginate(2);

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
                'id' => [
                    'required',
                    'integer',
                    /*'exists:news,id'*/
                    /*Rule::exists('news')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })*/
                ],
            ]
        )->validate();

        $news = News::withTrashed()->where('id' , $id)->first();

        if($news && !$news->deleted_at){
            $news->delete();

            if($news->trashed()){
                $output = ['success' => true , 'result' => 'news entity deleted'];
                $status = $this->status_success;
            }else{
                $output = ['success' => false , 'error' => "cannot delete news entity now , try again later"];
                $status = $this->status_server_error;
            }
        }else{
            $output = ['success' => false , 'error' => "selected news is either deleted or does not exists"];
            $status = $this->status_not_found;
        }

        return response()->json($output,$status);
    }
}
