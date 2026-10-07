{{-- One level of a category branch, recursing into each child's own children. --}}
<ul @class(['flex flex-col gap-1', 'ml-5 border-l border-outline pl-3 dark:border-outline-dark' => $nested])>
    @foreach ($branch->get($parentId, collect()) as $node)
        <li>
            <a class="text-sm underline-offset-2 hover:underline" href="{{ route('atrium.keystone.categories.show', $node) }}">{{ $node->label() }}</a>
            <span class="font-mono text-xs opacity-60">{{ $node->code }}</span>

            @if ($branch->has($node->id))
                @include('keystone::ui.categories.branch', ['parentId' => $node->id, 'nested' => true])
            @endif
        </li>
    @endforeach
</ul>
