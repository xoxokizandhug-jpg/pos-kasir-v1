<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        // filter: all | pending | done
        $filter = $request->query('filter', 'all');

        $query = Todo::query();

        if ($filter === 'pending') {
            $query->where('status', 'Belum Selesai');
        } elseif ($filter === 'done') {
            $query->where('status', 'Selesai');
        }

        $tasks = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return view('todolist.index', compact('tasks', 'filter'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:Selesai,Belum Selesai'],
            'prioritas' => ['required', 'in:Low,Medium,High'],
            'tanggal' => ['required', 'date'],
        ]);

        Todo::create($validated);

        return redirect()->route('todolist.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:Selesai,Belum Selesai'],
            'prioritas' => ['required', 'in:Low,Medium,High'],
            'tanggal' => ['required', 'date'],
        ]);

        $todo->update($validated);

        return redirect()->route('todolist.index')->with('success', 'Tugas berhasil diupdate.');
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return redirect()->route('todolist.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function toggle(Todo $todo, Request $request)
    {
        $checked = (int) $request->input('checked', 0);
        $todo->status = $checked === 1 ? 'Selesai' : 'Belum Selesai';
        $todo->save();

        // balik ke halaman yang sama (tetap bawa filter kalau ada)
        return redirect()->back()->with('success', 'Status tugas berhasil diubah.');
    }
}
