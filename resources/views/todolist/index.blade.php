@extends('layouts.app')

@section('title', 'UKK - To Do List')
@section('header_title', 'To-Do List')
@section('header_subtitle', 'Menu Opsi 1 & 2')

@section('content')
<div class="buy-form">

  {{-- ALERT --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- HEADER + MENU OPSI --}}
  <div class="row">
    <div class="col-sm-6 col-12">
      <h4 class="page-title">Aplikasi To-Do List (UKK)</h4>
      <small>
        Opsi 1: tambah tugas |
        Opsi 2: tampilkan tugas <b>(Belum Selesai)</b>
      </small>

      <div class="mt-2">
        @if(($filter ?? 'all') === 'pending')
          <span class="custom-badge status-orange">Mode: Belum Selesai</span>
        @elseif(($filter ?? 'all') === 'done')
          <span class="custom-badge status-green">Mode: Selesai</span>
        @else
          <span class="custom-badge status-grey">Mode: Semua</span>
        @endif
      </div>
    </div>

    <div class="col-sm-6 col-12 text-end m-b-30">
      {{-- OPSI 1 --}}
      <button type="button" class="btn btn-primary btn-rounded me-2"
              data-bs-toggle="modal" data-bs-target="#modalAddTask">
        <i class="fas fa-plus"></i> Opsi 1 - Tambah Tugas
      </button>

      {{-- OPSI 2 (filter pending) --}}
      <a href="{{ route('todolist.index', ['filter' => 'pending']) }}"
         class="btn btn-success btn-rounded me-2">
        <i class="fas fa-list"></i> Opsi 2 - Tampilkan (Belum Selesai)
      </a>

      {{-- tambahan biar bisa balik --}}
      <a href="{{ route('todolist.index') }}" class="btn btn-secondary btn-rounded">
        <i class="fas fa-sync"></i> Tampilkan Semua
      </a>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="row" id="section-daftar">
    <div class="col-md-12">
      <div class="table-responsive">
        <table class="table table-striped custom-table datatable">
          <thead>
            <tr>
              <th style="width:35%;">Nama Tugas</th>
              <th>Status</th>
              <th>Prioritas</th>
              <th>Tanggal</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @forelse($tasks as $t)
            <tr>
              <td>{{ $t->nama }}</td>

              {{-- STATUS: checkbox --}}
              <td>
                <form method="POST" action="{{ route('todolist.toggle', $t->id) }}" class="d-inline">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" name="checked" value="0">
                  <div class="d-flex align-items-center gap-2">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="checked"
                      value="1"
                      onchange="this.form.submit()"
                      {{ $t->status === 'Selesai' ? 'checked' : '' }}
                    >
                    @if($t->status === 'Selesai')
                      <span class="custom-badge status-green">Selesai</span>
                    @else
                      <span class="custom-badge status-orange">Belum Selesai</span>
                    @endif
                  </div>
                </form>
              </td>

              {{-- PRIORITAS --}}
              <td>
                @if($t->prioritas === 'High')
                  <span class="custom-badge status-red">High</span>
                @elseif($t->prioritas === 'Medium')
                  <span class="custom-badge status-blue">Medium</span>
                @else
                  <span class="custom-badge status-grey">Low</span>
                @endif
              </td>

              <td>{{ $t->tanggal }}</td>

              <td class="text-end">
                <div class="dropdown dropdown-action">
                  <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                  </a>

                  <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item btn-edit"
                       href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#modalEditTask"
                       data-id="{{ $t->id }}"
                       data-nama="{{ e($t->nama) }}"
                       data-status="{{ $t->status }}"
                       data-prioritas="{{ $t->prioritas }}"
                       data-tanggal="{{ $t->tanggal }}"
                    >
                      <i class="fas fa-pencil-alt m-r-5"></i> Edit
                    </a>

                    <a class="dropdown-item btn-delete"
                       href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#modalDeleteTask"
                       data-id="{{ $t->id }}"
                       data-nama="{{ e($t->nama) }}"
                    >
                      <i class="fas fa-trash-alt m-r-5"></i> Delete
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">
                <span class="custom-badge status-grey">Tidak ada data sesuai filter.</span>
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

{{-- MODAL ADD --}}
<div class="modal fade" id="modalAddTask" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Tugas Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('todolist.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Tugas</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="contoh: Kerjakan UKK">
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="Belum Selesai" {{ old('status','Belum Selesai')=='Belum Selesai'?'selected':'' }}>Belum Selesai</option>
              <option value="Selesai" {{ old('status')=='Selesai'?'selected':'' }}>Selesai</option>
            </select>
          </div>

          <div class="form-group">
            <label>Prioritas</label>
            <select name="prioritas" class="form-control">
              <option value="Low" {{ old('prioritas')=='Low'?'selected':'' }}>Low</option>
              <option value="Medium" {{ old('prioritas','Medium')=='Medium'?'selected':'' }}>Medium</option>
              <option value="High" {{ old('prioritas')=='High'?'selected':'' }}>High</option>
            </select>
          </div>

          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- MODAL EDIT (REUSABLE) --}}
<div class="modal fade" id="modalEditTask" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="formEdit" method="POST" action="#">
        @csrf
        @method('PUT')

        <div class="modal-body">
          <div class="form-group">
            <label>Nama Tugas</label>
            <input type="text" name="nama" id="editNama" class="form-control">
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status" id="editStatus" class="form-control">
              <option value="Belum Selesai">Belum Selesai</option>
              <option value="Selesai">Selesai</option>
            </select>
          </div>

          <div class="form-group">
            <label>Prioritas</label>
            <select name="prioritas" id="editPrioritas" class="form-control">
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
            </select>
          </div>

          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" id="editTanggal" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- MODAL DELETE (REUSABLE) --}}
<div id="modalDeleteTask" class="modal fade delete-modal" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="{{ asset('assets/img/sent.png') }}" alt="" width="50" height="46">
        <h3>Yakin mau hapus tugas ini?</h3>
        <p class="mb-3" id="deleteNama" style="font-weight:600;"></p>

        <form id="formDelete" method="POST" action="#">
          @csrf
          @method('DELETE')

          <div class="m-t-20">
            <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(function () {
    if ($('.datatable').length) {
      $('.datatable').DataTable();
    }

    // EDIT modal
    $('.btn-edit').on('click', function () {
      const id = $(this).data('id');
      $('#editNama').val($(this).data('nama'));
      $('#editStatus').val($(this).data('status'));
      $('#editPrioritas').val($(this).data('prioritas'));
      $('#editTanggal').val($(this).data('tanggal'));
      $('#formEdit').attr('action', `{{ url('/todolist') }}/${id}`);
    });

    // DELETE modal
    $('.btn-delete').on('click', function () {
      const id = $(this).data('id');
      $('#deleteNama').text($(this).data('nama'));
      $('#formDelete').attr('action', `{{ url('/todolist') }}/${id}`);
    });
  });
</script>
@endpush
