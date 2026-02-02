<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreFormRequest;
use App\Http\Requests\CMS\UpdateFormRequest;
use App\Http\Resources\CMS\FormResource;
use App\Models\CMS\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FormController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Form::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $forms = $query->orderBy('name')->get();

        return FormResource::collection($forms);
    }

    public function store(StoreFormRequest $request): FormResource
    {
        $form = Form::create($request->validated());

        return new FormResource($form->load(['site', 'creator']));
    }

    public function show(Form $form): FormResource
    {
        $form->load(['site', 'creator']);

        return new FormResource($form);
    }

    public function update(UpdateFormRequest $request, Form $form): FormResource
    {
        $form->update($request->validated());

        return new FormResource($form->load(['site', 'creator']));
    }

    public function destroy(Form $form): Response
    {
        $form->delete();

        return response()->noContent();
    }

    public function activate(Form $form): FormResource
    {
        $form->update(['active' => true]);

        return new FormResource($form);
    }

    public function deactivate(Form $form): FormResource
    {
        $form->update(['active' => false]);

        return new FormResource($form);
    }
}
