<ul class="nav nav-pills mb-3">
    <script>
        const items = [
            @foreach($entries as list($key, $value))
                ['{!! $key !!}', '{!! $value !!}'],
            @endforeach
        ];

        let active = location.hash;
        if (!items.some(item => active === '#' + item[0])) {
            active = '#{!! $entries[0][0] !!}';
        }

        for (const [name, html] of items) {
            const isActive = active === '#' + name;
            document.write(`<li class="nav-item">
                                    <a class="nav-link${isActive ? ' active' : ''}" id="${name}-tab" data-bs-toggle="tab" href="#${name}" aria-controls="${name}" aria-selected="${isActive ? 'true' : 'false'}">${html}</a>
                                </li>`);
            if (isActive) {
                setTimeout(() => document.getElementById(name).classList.add('active', 'show'), 1);
            }
        }
    </script>
</ul>
