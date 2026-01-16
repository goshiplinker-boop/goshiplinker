<x-layout>
    <x-slot name="title">System Logs</x-slot>

    <x-slot name="breadcrumbs">
        System Logs
    </x-slot>

    <x-slot name="page_header_title">
        <h1 class="page-header-title">System Logs</h1>
    </x-slot>

    <x-slot name="main">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-soft-success alert-dismissible">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-soft-danger alert-dismissible">
                {!! $errors->first() !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-grid gap-3 gap-lg-5">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="card-header-title">Application Logs</h4>

                    <div class="d-flex gap-2">
                        <select id="logLevel" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="ERROR">Error</option>
                            <option value="WARNING">Warning</option>
                            <option value="INFO">Info</option>
                        </select>

                        <button class="btn btn-sm btn-primary" onclick="loadLogs(1)">
                            Filter
                        </button>
                    </div>
                </div>

                <div class="card-body bg-dark text-light" style="height:500px; overflow:auto;">
                    <pre id="logOutput" class="mb-0">Loading logs...</pre>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-white btn-sm" onclick="loadLogs(currentPage - 1)">
                        ← Previous
                    </button>

                    <button class="btn btn-white btn-sm" onclick="loadLogs(currentPage + 1)">
                        Next →
                    </button>
                </div>
            </div>

        </div>
    </x-slot>
</x-layout>
 <script>
    let currentPage = 1;

    function formatLogLine(line) {
        try {
            const jsonStart = line.indexOf('{');
            if (jsonStart !== -1) {
                const json = JSON.parse(line.substring(jsonStart));
                return JSON.stringify(json, null, 2);
            }
        } catch (e) {}
        return line;
    }

    function loadLogs(page = 1) {
        if (page < 1) return;

        currentPage = page;
        const level = document.getElementById('logLevel').value;
        const output = document.getElementById('logOutput');

        output.textContent = 'Loading logs...';

        fetch(`{{ route('system.logs.fetch') }}?page=${page}&level=${level}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to load logs');
            return res.json();
        })
        .then(res => {
            if (!res.data || !res.data.length) {
                output.textContent = 'No logs found';
                return;
            }

            output.textContent = res.data
                .map(formatLogLine)
                .join("\n\n");
        })
        .catch(err => {
            output.textContent = 'Error loading logs';
            console.error(err);
        });
    }

    loadLogs();
</script>