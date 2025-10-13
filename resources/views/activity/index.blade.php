<!-- resources/views/activity/index.blade.php -->
<x-userlayout>
    <div class="space-y-6">
      


   <x-header.bar
  title="Activity Logs"
  subtitle="View system and user activities"
  :unread-count="$unreadCount ?? 0"
  :notifications="$notifications ?? collect()"
  :user="Auth::user()"
/>

@php
    // Friendly labels for action codes
    $actionLabels = [
        'auth.login'                      => 'Logged in',
        'auth.logout'                     => 'Logged out',

        'title.created'                   => 'Title created',
        'title.status_changed'            => 'Title status changed',
        'title.adviser_assigned'          => 'Adviser assigned',
        'title.final_document_set'        => 'Final document set',
        'title.deleted'                   => 'Title deleted',
        'title.awaiting_admin'            => 'Title awaiting admin',
        'title.admin.approved'            => 'Title approved by admin',
        'title.admin.rejected'            => 'Title rejected by admin',

        'announcement.created'            => 'Announcement created',
        'announcement.updated'            => 'Announcement updated',
        'announcement.deleted'            => 'Announcement deleted',

        'paper.uploaded'                  => 'Research paper uploaded',
        'paper.deleted'                   => 'Research paper deleted',
        'paper.chain_status_changed'      => 'Paper chain status changed',
        'paper.tx_set'                    => 'Paper tx set',
        'paper.blockchain.registered'     => 'Paper registered on chain',
        'paper.blockchain.confirmed'      => 'Paper confirmed on chain',
        'paper.blockchain.failed'         => 'Paper chain failed',

        'document.created'                => 'Document created',
        'document.plagiarism_updated'     => 'Plagiarism updated',
        'document.plagiarism_internal_updated' => 'Internal similarity updated',
        'document.plagiarism_external_updated' => 'External similarity updated',
        'document.file_updated'           => 'Document file updated',

        'adviser_request.created'         => 'Adviser request created',
        'adviser_request.status_changed'  => 'Adviser request status changed',
        'adviser_request.accepted'        => 'Adviser request accepted',
        'adviser_request.rejected'        => 'Adviser request rejected',

        'blockchain.request.created'      => 'Blockchain request created',
        'blockchain.request.status_changed'=> 'Blockchain request status changed',
        'blockchain.request.approved'     => 'Blockchain request approved',
        'blockchain.request.rejected'     => 'Blockchain request rejected',
     // --- USER PROFILE / MANAGEMENT ---
'user.created'                      => 'User created',
'user.updated'                      => 'User updated',
'user.deleted'                      => 'User deleted',
'user.role_changed'                 => 'User role changed',
'user.status_changed'               => 'User status changed',
'user.email_changed'                => 'User email changed',
'user.avatar_updated'               => 'User avatar updated',
'user.password_changed'             => 'User password changed',
'user.verified'                     => 'User email verified',


    ];
@endphp

        {{-- Filters --}}
{{-- Filters --}}
<form method="GET" class="bg-white rounded-lg shadow p-4 md:p-5">
  <div class="flex flex-col gap-3">

    {{-- Row: inputs --}}
    <div class="flex flex-wrap items-end gap-3">

      {{-- Name (search) – first in row (admin only) --}}
      @if ($isAdmin)
        <div class="flex flex-col">
          <label class="text-xs font-semibold text-gray-600 mb-1">Name</label>
          <input
            type="text"
            name="who"
            value="{{ request('who') }}"
            placeholder="Search name"
            class="h-10 w-56 md:w-64 border border-gray-300 rounded-xl px-3 text-sm shadow-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      @endif

      {{-- Action (dropdown pill) --}}
      <div class="flex flex-col">
        <label class="text-xs font-semibold text-gray-600 mb-1">Action</label>
        <div class="relative">
          <select
            name="action"
            class="h-10 min-w-[9rem] appearance-none pl-3 pr-8 border border-gray-300 rounded-xl bg-white text-sm shadow-sm 
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All</option>
            @foreach(($actions ?? []) as $act)
              @php
                $label = $actionLabels[$act] ?? \Illuminate\Support\Str::headline(str_replace('.', ' ', $act));
              @endphp
              <option value="{{ $act }}" @selected(request('action') === $act)>{{ $label }}</option>
            @endforeach
          </select>
          {{-- Chevron --}}
          <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </span>
        </div>
      </div>

      {{-- Role (dropdown pill) – admin only, after Action --}}
      @if ($isAdmin)
        <div class="flex flex-col">
          <label class="text-xs font-semibold text-gray-600 mb-1">Role</label>
          <div class="relative">
            <select
              name="role"
              class="h-10 min-w-[8rem] appearance-none pl-3 pr-8 border border-gray-300 rounded-xl bg-white text-sm shadow-sm
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All</option>
              <option value="ADMIN"   @selected(request('role')==='ADMIN')>Admin</option>
              <option value="ADVISER" @selected(request('role')==='ADVISER')>Adviser</option>
              <option value="STUDENT" @selected(request('role')==='STUDENT')>Student</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
              </svg>
            </span>
          </div>
        </div>
      @endif

      {{-- From --}}
      <div class="flex flex-col">
        <label class="text-xs font-semibold text-gray-600 mb-1">From</label>
        <input
          type="date"
          name="from"
          value="{{ request('from') }}"
          class="h-10 border border-gray-300 rounded-xl px-3 text-sm shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      {{-- To --}}
      <div class="flex flex-col">
        <label class="text-xs font-semibold text-gray-600 mb-1">To</label>
        <input
          type="date"
          name="to"
          value="{{ request('to') }}"
          class="h-10 border border-gray-300 rounded-xl px-3 text-sm shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      {{-- Per page (tiny pill) --}}
      <div class="flex flex-col">
        <label class="text-xs font-semibold text-gray-600 mb-1">Per page</label>
        <div class="relative">
          <select
            name="per_page"
            class="h-10 w-[4.5rem] appearance-none pl-3 pr-7 border border-gray-300 rounded-xl bg-white text-sm shadow-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            @foreach([10,20,50,100] as $opt)
              <option value="{{ $opt }}" @selected(($perPage ?? 20)===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </span>
        </div>
      </div>

      {{-- Spacer flex so buttons float right on wide screens --}}
      <div class="flex-1"></div>

      {{-- Buttons --}}
      <div class="flex gap-2">
        <button class="h-10 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm shadow-sm">Apply</button>
        <a href="{{ url()->current() }}"
           class="h-10 px-4 flex items-center border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 text-sm">
          Reset
        </a>
      </div>

    </div>
  </div>
</form>




        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-x-auto p-3">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
               <tr>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">When</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
</tr>

                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse ($logs as $log)
                   @php


    $labels = [
        'auth.login'                        => 'Logged in',
        'auth.logout'                       => 'Logged out',

        'title.created'                     => 'Title created',
        'title.status_changed'              => 'Title status changed',
        'title.adviser_assigned'            => 'Adviser assigned',
        'title.final_document_set'          => 'Final document set',
        'title.deleted'                     => 'Title deleted',
        'title.awaiting_admin'              => 'Sent to admin approval',
        'title.admin.approved'              => 'Admin approved',
        'title.admin.rejected'              => 'Admin rejected',

        'announcement.created'              => 'Announcement created',
        'announcement.updated'              => 'Announcement updated',
        'announcement.deleted'              => 'Announcement deleted',

        'paper.uploaded'                    => 'Research paper uploaded',
        'paper.deleted'                     => 'Research paper deleted',
        'paper.chain_status_changed'        => 'Paper chain status changed',
        'paper.tx_set'                      => 'Paper tx set',
        'paper.blockchain.registered'       => 'Paper registered on chain',
        'paper.blockchain.confirmed'        => 'Paper confirmed on chain',
        'paper.blockchain.failed'           => 'Paper chain failed',

        'document.created'                  => 'Document created',
        'document.plagiarism_updated'       => 'Plagiarism updated',
        'document.plagiarism_internal_updated' => 'Internal similarity updated',
        'document.plagiarism_external_updated' => 'External similarity updated',
        'document.file_updated'             => 'Document file updated',

        // base fallbacks for adviser requests (we’ll override below)
        'adviser_request.created'           => 'Adviser request created',
        'adviser_request.status_changed'    => 'Adviser request updated',
        'adviser_request.accepted'          => 'Request accepted',
        'adviser_request.rejected'          => 'Request declined',

        'blockchain.request.created'        => 'Blockchain request created',
        'blockchain.request.status_changed' => 'Blockchain request updated',
        'blockchain.request.approved'       => 'Blockchain request approved',
        'blockchain.request.rejected'       => 'Blockchain request rejected',
        // --- USER PROFILE / MANAGEMENT ---
'user.created'                      => 'User created',
'user.updated'                      => 'User updated',
'user.deleted'                      => 'User deleted',
'user.role_changed'                 => 'User role changed',
'user.status_changed'               => 'User status changed',
'user.email_changed'                => 'User email changed',
'user.avatar_updated'               => 'User avatar updated',
'user.password_changed'             => 'User password changed',
'user.verified'                     => 'User email verified',

    ];

    $friendly = $labels[$log->action] ?? Str::headline(str_replace('.', ' ', $log->action));

    // --- Friendly overrides to match your flow exactly ---
    $s = $log->subject; // polymorphic model instance

    if ($s instanceof \App\Models\AdviserRequest) {
        $who = ($s->requested_by === 'adviser') ? 'Adviser' : 'Student';

        if ($log->action === 'adviser_request.created') {
            // “Student request” or “Adviser request”
            $friendly = "{$who} request";
        } elseif ($log->action === 'adviser_request.status_changed') {
            // keep concise; if you want to show final status, append "to {$s->status}"
            $friendly = "{$who} request updated";
        } elseif ($log->action === 'adviser_request.accepted') {
            $friendly = 'Request accepted';
        } elseif ($log->action === 'adviser_request.rejected') {
            $friendly = 'Request declined';
        }
    }

    if ($s instanceof \App\Models\Title) {
        if ($log->action === 'title.awaiting_admin') {
            $friendly = 'Sent to admin approval';
        } elseif ($log->action === 'title.admin.approved') {
            $friendly = 'Admin approved';
        } elseif ($log->action === 'title.admin.rejected') {
            $friendly = 'Admin rejected';
        }
    }
@endphp


                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                               {{ $log->created_at->format('F d Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                            {{ $friendly }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                            {{ $log->user?->name ?? '—' }} <sup class="text-gray-400 ms-1"> {{ $log->role ? "$log->role" : '' }}</sup>
                        </td>
       
                             <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            {{ $log->ip ?? 'N/A' }}
                        </td>

 
{{-- IP column moved before Subject, so keep your IP <td> ABOVE this one --}}
<td class="px-6 py-4 whitespace-nowrap">
  @php $s = $log->subject; @endphp

  @if(!$s)
    <span class="text-gray-500 italic">No subject</span>
  @else
    @php $modalId = 'subject-modal-'.$log->id; @endphp
  <button
  type="button"
  onclick="openActivityModal('{{ $modalId }}')"
  class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 shadow-sm"
  title="View subject details"
>
  Details
  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M10 3c-4.5 0-8 5-8 7s3.5 7 8 7 8-5 8-7-3.5-7-8-7Zm0 11a4 4 0 100-8 4 4 0 000 8Z" clip-rule="evenodd"/>
  </svg>
</button>

{{-- Modal (clean + pro) --}}
<div id="{{ $modalId }}" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
  {{-- Backdrop --}}
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeActivityModal('{{ $modalId }}')"></div>

  {{-- Dialog --}}
  <div class="relative mx-auto my-12 mt-20 w-[92%] max-w-xl">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5">

      {{-- Header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="min-w-0">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7s-8-3.134-8-7 3.582-7 8-7 8 3.134 8 7Zm-8 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM9 6h2v5H9V6Z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="truncate">
              <h3 id="{{ $modalId }}-title" class="truncate text-base font-semibold text-gray-900">Activity Subject</h3>
              <p class="text-xs text-gray-500">#{{ $log->id }} • {{ $log->created_at->format('F d Y, h:i A') }}</p>
            </div>
          </div>
        </div>
        <button type="button"
                class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                onclick="closeActivityModal('{{ $modalId }}')" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 1 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>

      {{-- Body --}}
      <div class="px-5 py-4">
        @php
          $s = $log->subject;
          // pill helper
          $chip = fn($text, $tone='gray') =>
            "<span class=\"inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-{$tone}-100 text-{$tone}-700 border border-{$tone}-200\">{$text}</span>";
        @endphp

        {{-- STATUS / TYPE ROW (optional chips) --}}
        @if ($s instanceof \App\Models\AdviserRequest)
          <div class="mb-3 flex flex-wrap gap-2">
            {!! $s->requested_by ? $chip(ucfirst($s->requested_by).' request','blue') : '' !!}
            {!! $s->status ? $chip($s->status, $s->status === 'accepted' ? 'green' : ($s->status === 'rejected' ? 'red' : 'gray')) : '' !!}
          </div>
        @elseif ($s instanceof \App\Models\BlockchainRequest)
          <div class="mb-3 flex flex-wrap gap-2">
            {!! $s->type ? $chip(\Illuminate\Support\Str::headline($s->type),'purple') : '' !!}
            {!! $s->status ? $chip(\Illuminate\Support\Str::headline($s->status), $s->status === 'approved' ? 'green' : ($s->status === 'rejected' ? 'red' : 'gray')) : '' !!}
          </div>
        @endif

        {{-- UNIFORM SPEC GRID (labels left fixed, values right) --}}
      {{-- UNIFORM SPEC GRID (clean, wraps nicely) --}}
<dl class="grid grid-cols-12 gap-y-2 text-sm">
  @php
    $dt = 'col-span-3 pr-3 text-[11px] font-semibold tracking-wide text-gray-500 uppercase';
    $dd = 'col-span-9 text-gray-900 break-words whitespace-pre-wrap leading-6';
  @endphp

  @if ($s instanceof \App\Models\User)
    <dt class="{{ $dt }}">Name</dt><dd class="{{ $dd }}">{{ $s->name }}</dd>
    <dt class="{{ $dt }}">Email</dt><dd class="{{ $dd }}">{{ $s->email }}</dd>
    <dt class="{{ $dt }}">Role</dt><dd class="{{ $dd }}">{{ $s->role }}</dd>
    <dt class="{{ $dt }}">Status</dt><dd class="{{ $dd }}">{{ $s->is_active ? 'Active' : 'Disabled' }}</dd>

  @elseif ($s instanceof \App\Models\AdviserRequest)
    <dt class="{{ $dt }}">Title</dt><dd class="{{ $dd }}">{{ $s->title?->title ?? '—' }}</dd>
    <dt class="{{ $dt }}">Adviser</dt><dd class="{{ $dd }}">{{ $s->adviser?->name ?? ('#'.$s->adviser_id) }}</dd>

  @elseif ($s instanceof \App\Models\Title)
    <dt class="{{ $dt }}">Title</dt><dd class="{{ $dd }}">{{ $s->title }}</dd>
    @if(method_exists($s, 'adviser') && $s->adviser?->name)
      <dt class="{{ $dt }}">Adviser</dt><dd class="{{ $dd }}">{{ $s->adviser->name }}</dd>
    @elseif($s->owner?->name)
      <dt class="{{ $dt }}">Owner</dt><dd class="{{ $dd }}">{{ $s->owner->name }}</dd>
    @endif

  @elseif ($s instanceof \App\Models\Document)
    <dt class="{{ $dt }}">Chapter</dt><dd class="{{ $dd }}">{{ $s->chapter ?? '—' }}</dd>
    @if($s->title?->title)
      <dt class="{{ $dt }}">Parent Title</dt><dd class="{{ $dd }}">{{ $s->title->title }}</dd>
    @endif

  @elseif ($s instanceof \App\Models\ResearchPaper)
    <dt class="{{ $dt }}">Paper</dt><dd class="{{ $dd }}">{{ $s->title ?? 'Untitled paper' }}</dd>
    @if($s->user?->name)
      <dt class="{{ $dt }}">Author</dt><dd class="{{ $dd }}">{{ $s->user->name }}</dd>
    @endif

  @elseif ($s instanceof \App\Models\Announcement)
    <dt class="{{ $dt }}">Announcement</dt><dd class="{{ $dd }}">{{ $s->title ?? 'Announcement' }}</dd>

  @elseif ($s instanceof \App\Models\BlockchainRequest)
    <dt class="{{ $dt }}">Paper</dt><dd class="{{ $dd }}">{{ $s->paper?->title ?? '—' }}</dd>

  @elseif ($s)
    <dt class="{{ $dt }}">Type</dt><dd class="{{ $dd }}">{{ class_basename($log->subject_type) }}</dd>
    <dt class="{{ $dt }}">ID</dt><dd class="{{ $dd }}">#{{ $log->subject_id }}</dd>

  @else
    <dt class="{{ $dt }}">Subject</dt><dd class="{{ $dd }} text-gray-500 italic">No subject</dd>
  @endif
</dl>


        {{-- CHANGE DETAILS (pretty print if present) --}}
       {{-- CHANGE DETAILS (chips + arrow if from/to available) --}}
@php $changes = $log->meta ?? []; @endphp
@if(!empty($changes) && is_array($changes))
  <div class="mt-4">
    <div class="text-xs font-semibold text-gray-600 mb-1">Change details</div>

    @php
      $pretty = fn($v) => is_string($v)
        ? \Illuminate\Support\Str::headline(str_replace('_',' ', $v))
        : (is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE));

      $chip = fn($text, $tone='gray') =>
        "<span class=\"inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-{$tone}-100 text-{$tone}-700 border border-{$tone}-200\">{$text}</span>";

      $hasFromTo = isset($changes['from'], $changes['to']);
      $fromTone = 'gray';
      $toTone   = 'blue';
      if ($hasFromTo) {
        $fromStr = $pretty($changes['from']);
        $toStr   = $pretty($changes['to']);
        // tone suggestions for status names:
        if (is_string($changes['to'])) {
          $low = strtolower($changes['to']);
          $toTone = $low === 'approved' || $low === 'accepted' ? 'green'
                   : ($low === 'rejected' || $low === 'declined' ? 'red' : 'blue');
        }
      }
    @endphp

    @if($hasFromTo)
      <div class="flex items-center gap-2 text-sm">
        {!! $chip($fromStr, $fromTone) !!}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        {!! $chip($toStr, $toTone) !!}
      </div>
    @else
      @php
        $isAssoc = static function(array $a){ return $a !== [] && array_keys($a)!==range(0,count($a)-1); };
      @endphp
      @if($isAssoc($changes))
        <dl class="grid grid-cols-[160px,1fr] gap-x-6 gap-y-1.5 text-sm">
          @foreach($changes as $k => $v)
            <dt class="text-xs font-medium text-gray-500">{{ \Illuminate\Support\Str::headline($k) }}</dt>
            <dd class="text-gray-900">
              @if(is_array($v) || is_object($v))
                <pre class="whitespace-pre-wrap break-words rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-xs text-gray-800">{{ json_encode($v, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
              @else
                {{ $pretty($v) }}
              @endif
            </dd>
          @endforeach
        </dl>
      @else
        <pre class="whitespace-pre-wrap break-words rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800">{{ json_encode($changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
      @endif
    @endif
  </div>
@endif

      </div>

      {{-- Footer --}}
      <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-3">
        <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm hover:bg-gray-50"
                onclick="closeActivityModal('{{ $modalId }}')">Close</button>
      </div>
    </div>
  </div>
</div>

 
  @endif
</td>

                   
                   
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-500">No activity found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

                {{-- Pagination --}}
{{-- Pagination --}}
@if ($logs->hasPages())
  <div class="mt-4">
    {!! $logs->onEachSide(1)->links() !!}
  </div>
@endif
        </div>


<style>
td{
  font-size: 12px !important;
  padding: 12px;
}
</style>

<script>
  function openActivityModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('hidden');
    // basic focus trap-ish: focus dialog close button
    setTimeout(() => {
      const btn = el.querySelector('button[aria-label="Close"]');
      btn?.focus();
    }, 0);
    document.body.style.overflow = 'hidden';
  }
  function closeActivityModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    document.body.style.overflow = '';
  }
  // Escape key closes the top-most open modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const open = [...document.querySelectorAll('[id^="subject-modal-"]')].find(m => !m.classList.contains('hidden'));
      if (open) closeActivityModal(open.id);
    }
  });
</script>

        {{-- Pagination --}}


    </div>

     
</x-userlayout>
