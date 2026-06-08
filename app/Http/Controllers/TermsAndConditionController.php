<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use App\Modules\Http\Message;

class TermsAndConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Message $message)
    {
        $terms = TermsAndCondition::orderBy('created_at', 'desc')->paginate(10);

        $message->setContent(200, 'Terms retrieved', '', [
            'terms' => $terms,
        ]);

        return $message->render();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Message $message)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'paragraph' => 'required|string|max:10000',
        ]);

        $term = TermsAndCondition::create($validated);

        $message->setContent(201, 'Terms created', '', [
            'term' => $term,
        ]);

        return $message->render();
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Message $message)
    {
        $term = TermsAndCondition::find($id);

        if (!$term) {
            return $message->setContent(404, 'Terms not found')->render();
        }

        $message->setContent(200, 'Terms retrieved', '', [
            'term' => $term,
        ]);

        return $message->render();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, Message $message)
    {
        $term = TermsAndCondition::find($id);

        if (!$term) {
            $message->setContent(404, 'Terms not found');
            return $message->render();
        }

        $validated = $request->validate([
            'title'     => 'sometimes|required|string|max:255',
            'paragraph' => 'sometimes|required|string|max:10000',
        ]);

        $term->update($validated);

        $message->setContent(200, 'Terms updated', '', [
            'term' => $term->fresh(),
        ]);

        return $message->render();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Message $message)
    {
        $term = TermsAndCondition::find($id);

        if (!$term) {
            $message->setContent(404, 'Terms not found');
            return $message->render();
        }

        $term->delete();

        $message->setContent(200, 'Terms deleted');

        return $message->render();
    }
}