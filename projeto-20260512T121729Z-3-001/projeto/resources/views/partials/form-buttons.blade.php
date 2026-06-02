@php
    $deleteForm = $deleteForm ?? 'delete-form';
    $new = $new ?? false;
    $show = $show ?? false;
    $edit = $edit ?? false;
    $delete = $delete ?? false;
    $save = $save ?? false;
    $cancel = $cancel ?? false;
@endphp
<div class="mt-6 flex flex-wrap justify-start items-center gap-4">
    @if($new)
        <flux:button variant="primary" href="{{ route($entity . 's.create') }}">New</flux:button>
    @endif
    @if($show)
        <flux:button variant="filled" class="uppercase" href="{{ route($entity . 's.show', $value) }}">Show</flux:button>
    @endif
    @if($edit)
        <flux:button variant="filled" class="uppercase" href="{{ route($entity . 's.edit', $value) }}">Edit</flux:button>
    @endif
    @if($delete)
        <flux:button variant="danger" type="submit" form="{{ $deleteForm }}" class="uppercase">Delete</flux:button>
    @endif
    <div class="grow"></div>
    @if($save)
        <flux:button variant="primary" type="submit" class="uppercase">Save</flux:button>
    @endif
    @if($cancel)
        <flux:button variant="filled" class="uppercase" href="{{ url()->full() }}">Cancel</flux:button>
    @endif
</div>
