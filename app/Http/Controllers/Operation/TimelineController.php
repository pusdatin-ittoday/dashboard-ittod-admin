<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\EventTimeline;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TimelineController extends Controller
{
    private function checkAccess()
    {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia']), 403);
    }

    // Menampilkan daftar lini masa kegiatan non-kompetisi (REQ-10)
    public function index()
    {
        $this->checkAccess();
        $query = EventTimeline::with('event')
            ->whereHas('event', fn ($query) => $query->where('type', 'non_competition'))
            ->orderBy('date', 'asc');
            
        if (auth()->user()->role === 'panitia') {
            $query->whereIn('event_id', auth()->user()->events->pluck('id'));
        }

        $timelines = $query->get();

        return view('operation.timeline.index', compact('timelines'));
    }

    // Menampilkan form tambah lini masa (REQ-10)
    public function create()
    {
        $this->checkAccess();
        $query = Event::where('type', 'non_competition')->orderBy('title');
        
        if (auth()->user()->role === 'panitia') {
            $query->whereIn('id', auth()->user()->events->pluck('id'));
        }
        
        $events = $query->get();

        return view('operation.timeline.create', compact('events'));
    }

    public function storeEvent(Request $request)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'required|string|max:2000',
            'guide_book_url' => 'required|url|max:500',
            'price' => 'nullable|integer|min:0',
            'contact_person1' => 'required|string|max:191',
            'contact_person2' => 'nullable|string|max:191',
        ]);

        Event::create([
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'type' => 'non_competition',
            'description' => $validated['description'],
            'guide_book_url' => $validated['guide_book_url'],
            'price' => $validated['price'] ?? 0,
            'contact_person1' => $validated['contact_person1'],
            'contact_person2' => $validated['contact_person2'] ?? null,
            'max_noncompetition_participant' => null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kegiatan baru berhasil ditambahkan. Silakan pilih kegiatan tersebut untuk membuat lini masa.');
    }

    // Menyimpan lini masa baru (REQ-10)
    public function store(Request $request)
    {
        $this->checkAccess();
        
        $eventRules = [
            'required',
            Rule::exists('event', 'id')->where(fn ($query) => $query->where('type', 'non_competition')),
        ];
        
        if (auth()->user()->role === 'panitia') {
            $eventRules[] = Rule::in(auth()->user()->events->pluck('id')->toArray());
        }

        $request->validate([
            'event_id' => $eventRules,
            'title' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        EventTimeline::create([
            'event_id' => $request->event_id,
            'title' => $request->title,
            'date' => $request->date,
        ]);

        return redirect()->route('timeline.index')->with('success', 'Lini masa berhasil ditambahkan!');
    }

    // Menampilkan form edit lini masa (REQ-10)
    public function edit(string $id)
    {
        $this->checkAccess();
        $timeline = EventTimeline::whereHas('event', fn ($query) => $query->where('type', 'non_competition'))->findOrFail($id);
        
        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $timeline->event_id), 403);
        }

        $query = Event::where('type', 'non_competition')->orderBy('title');
        if (auth()->user()->role === 'panitia') {
            $query->whereIn('id', auth()->user()->events->pluck('id'));
        }
        $events = $query->get();

        return view('operation.timeline.edit', compact('timeline', 'events'));
    }

    // Memperbarui lini masa (REQ-10)
    public function update(Request $request, string $id)
    {
        $this->checkAccess();
        $timeline = EventTimeline::whereHas('event', fn ($query) => $query->where('type', 'non_competition'))->findOrFail($id);
        
        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $timeline->event_id), 403);
        }

        $eventRules = [
            'required',
            Rule::exists('event', 'id')->where(fn ($query) => $query->where('type', 'non_competition')),
        ];
        
        if (auth()->user()->role === 'panitia') {
            $eventRules[] = Rule::in(auth()->user()->events->pluck('id')->toArray());
        }

        $request->validate([
            'event_id' => $eventRules,
            'title' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        $timeline->update([
            'event_id' => $request->event_id,
            'title' => $request->title,
            'date' => $request->date,
        ]);

        return redirect()->route('timeline.index')->with('success', 'Lini masa berhasil diperbarui!');
    }

    // Menghapus lini masa (REQ-10)
    public function destroy(string $id)
    {
        $this->checkAccess();
        $timeline = EventTimeline::whereHas('event', fn ($query) => $query->where('type', 'non_competition'))->findOrFail($id);
        
        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $timeline->event_id), 403);
        }
        
        $timeline->delete();

        return redirect()->route('timeline.index')->with('success', 'Lini masa berhasil dihapus!');
    }
}
