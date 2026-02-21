<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Log Lens</title>
    <link rel="stylesheet" href="{{ asset('vendor/loglens/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/loglens/css/sweetalert2.min.css') }}">
    <script src="{{ asset('vendor/loglens/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('vendor/loglens/js/lucide.min.js') }}"></script>

</head>

<body>
    <div class="container">
        <header>
            <div class="brand-wrapper">
                <h1><img src="{{ asset('vendor/loglens/images/logo.png') }}" alt="Logo" class="brand-logo" />Laravel
                    Log Lens</h1>
                <div class="brand-ver">
                    <span class="ver">Version : 1.0.0</span>
                    <a class="dev" target="_blank" href="https://github.com/dante-san">Laxmidhar Maharana</a>
                </div>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Current File</div>
                    <div class="stat-value">{{ basename($currentFile) }}</div>
                </div>
                <div class="stat-card" onclick="toggleFilter('all')" data-filter="all">
                    <div class="stat-label">Total Entries</div>
                    <div class="stat-value" id="totalCount">{{ $totalEntries }}</div>
                </div>
                <div class="stat-card error" onclick="toggleFilter('error')" data-filter="error">
                    <div class="stat-label">Errors</div>
                    <div class="stat-value" style="color: #ff6b6b;">{{ $stats['error'] ?? 0 }}</div>
                </div>
                <div class="stat-card warning" onclick="toggleFilter('warning')" data-filter="warning">
                    <div class="stat-label">Warnings</div>
                    <div class="stat-value" style="color: #ffd93d;">{{ $stats['warning'] ?? 0 }}</div>
                </div>
                <div class="stat-card info" onclick="toggleFilter('info')" data-filter="info">
                    <div class="stat-label">Infos</div>
                    <div class="stat-value" style="color: #6bcf7f;">{{ $stats['info'] ?? 0 }}</div>
                </div>
                <div class="stat-card debug" onclick="toggleFilter('debug')" data-filter="debug">
                    <div class="stat-label">Debugs</div>
                    <div class="stat-value" style="color: #74b9ff;">{{ $stats['debug'] ?? 0 }}</div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <span>{{ session('success') }}</span>
                <button onclick="closeAlert(this)"
                    style="margin-left: auto; background: none; border: none; color: #6bcf7f; cursor: pointer; font-size: 20px;">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i data-lucide="alert-circle"></i>
                <span>{{ session('error') }}</span>
                <button onclick="closeAlert(this)"
                    style="margin-left: auto; background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 20px;">&times;</button>
            </div>
        @endif

        <div class="controls">
            <div class="control-row">
                {{-- <div class="file-selector">
                    <label for="logFile" class="label-proper">
                        <i data-lucide="folder-open"></i>
                        <span>Log File</span>
                    </label>
                    <select id="logFile" onchange="changeLogFile(this.value)">
                        @foreach ($logFiles as $file)
                            <option value="{{ basename($file) }}" {{ $file === $currentFile ? 'selected' : '' }}>
                                {{ basename($file) }} ({{ $fileSizes[$file] ?? '0 B' }})
                            </option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="file-selector">
                    <label for="logFile" class="label-proper">
                        <i data-lucide="folder-open"></i>
                        <span>Log File</span>
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="logFile" onchange="changeLogFile(this.value)" style="flex: 1;">
                            @foreach ($logFiles as $file)
                                <option value="{{ basename($file) }}" {{ $file === $currentFile ? 'selected' : '' }}>
                                    {{ basename($file) }} ({{ $fileSizes[$file] ?? '0 B' }})
                                </option>
                            @endforeach
                        </select>

                        <label for="browseFile" class="btn btn-danger"
                            style="margin: 0; cursor: pointer; padding: 12px 20px;color:white;">
                            <i data-lucide="upload"></i>
                            <span>Browse</span>
                        </label>
                        <input type="file" id="browseFile" accept=".log" onchange="uploadLogFile(this)"
                            style="display: none;">
                    </div>
                </div>

                <div class="search-box">
                    <label for="logFile" class="label-proper">
                        <i data-lucide="search"></i>
                        <span>Search
                            More</span>
                    </label>
                    <input type="text" id="searchInput" placeholder="Type..." onkeyup="filterLogs()">
                </div>
            </div>

            <div class="control-row">
                <div class="filter-group">
                    <button class="filter-btn active" onclick="toggleFilter('all')" data-filter="all">All</button>
                    <button class="filter-btn error" onclick="toggleFilter('error')" data-filter="error">Errors</button>
                    <button class="filter-btn warning" onclick="toggleFilter('warning')"
                        data-filter="warning">Warnings</button>
                    <button class="filter-btn info" onclick="toggleFilter('info')" data-filter="info">Info</button>
                    <button class="filter-btn debug" onclick="toggleFilter('debug')"
                        data-filter="debug">Debug</button>
                </div>
                <div class="control-row">
                    <div class="actions">
                        <button onclick="location.reload()" class="btn btn-primary">
                            <i data-lucide="refresh-cw"></i> Refresh
                        </button>
                        <a href="{{ route('loglens.download', ['file' => basename($currentFile)]) }}"
                            class="btn btn-success">
                            <i data-lucide="download"></i> Download
                        </a>
                        <form id="clearLogForm"
                            action="{{ route('loglens.clear', ['file' => basename($currentFile)]) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            <button type="button" onclick="confirmClearLog()" class="btn btn-danger">
                                <i data-lucide="trash-2"></i>
                                Clear Log
                            </button>
                        </form>
                    </div>
                    <button class="auto-scroll-toggle label-proper" onclick="toggleAutoScroll()" id="autoScrollBtn">
                        <i data-lucide="arrow-down"></i>
                        <span>Auto-scroll: OFF</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="log-container">
            <div class="log-header">
                <span class="log-title">Log Entries</span>
                <span class="log-count" id="visibleCount">{{ count($logEntries) }} entries</span>
            </div>
            <div class="log-content" id="logContent">
                @foreach ($logEntries as $entry)
                    <div class="log-entry {{ strtolower($entry['level']) }}"
                        data-level="{{ strtolower($entry['level']) }}"
                        data-message="{{ strtolower($entry['message']) }}">
                        <div style="margin-bottom: .3rem">
                            <span class="log-level {{ strtolower($entry['level']) }}">{{ $entry['level'] }}</span>
                            <span class="log-timestamp">{{ $entry['timestamp'] }} -
                                {{ \Carbon\Carbon::parse($entry['timestamp'])->diffForHumans() }}</span>
                        </div>
                        <div class="log-message">{{ $entry['message'] }}</div>
                        @if (!empty($entry['context']))
                            <div class="log-context">{{ $entry['context'] }}</div>
                        @endif
                    </div>
                @endforeach
                <div class="empty-state hidden" id="dynamicEmptyState">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">No Logs Found</div>
                    <div class="empty-message">This log file is empty or doesn't contain any entries yet.</div>
                </div>
            </div>
            {{-- @if ($totalPages > 1)
                <div style="display:flex; justify-content:center; gap:10px; padding:20px; background:#1e1e1e;">
                    @for ($i = 1; $i <= $totalPages; $i++)
                        <a href="?file={{ basename($currentFile) }}&page={{ $i }}"
                            class="btn {{ $i == $currentPage ? 'btn-primary' : '' }}"
                            style="padding: 8px 14px;">{{ $i }}</a>
                    @endfor
                </div>
            @endif --}}
        </div>
    </div>
    <script src="{{ asset('modules/logviewer/js/lucide.min.js') }}"></script>
    <script>
        let activeFilter = 'all';
        let autoScroll = false;

        function changeLogFile(file) {
            window.location.href = '{{ route('loglens.index') }}?file=' + encodeURIComponent(file);
        }

        function toggleFilter(level) {
            if (!level || typeof level !== 'string') return;

            activeFilter = level;

            document.querySelectorAll('.filter-btn.active')
                .forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.stat-card.active')
                .forEach(el => el.classList.remove('active'));

            document.querySelectorAll(`.filter-btn[data-filter="${level}"]`)
                .forEach(el => el.classList.add('active'));
            document.querySelectorAll(`.stat-card[data-filter="${level}"]`)
                .forEach(el => el.classList.add('active'));

            const visibleCountEl = document.getElementById('visibleCount');

            if (visibleCountEl) {
                visibleCountEl.classList.remove('error', 'warning', 'info', 'debug', 'all');
                visibleCountEl.classList.add(level);
            }

            try {
                filterLogs();
            } catch (e) {
                console.error('filterLogs() failed:', e);
            }

        }

        // function filterLogs() {
        //     const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        //     const entries = document.querySelectorAll('.log-entry');
        //     let visibleCount = 0;

        //     entries.forEach(entry => {
        //         const level = entry.dataset.level;
        //         const message = entry.dataset.message;

        //         let levelMatch = (activeFilter === 'all' || level === activeFilter);
        //         let searchMatch = (searchTerm === '' || message.includes(searchTerm));

        //         if (levelMatch && searchMatch) {
        //             entry.classList.remove('hidden');
        //             visibleCount++;
        //         } else {
        //             entry.classList.add('hidden');
        //         }
        //     });

        //     document.getElementById('visibleCount').textContent = visibleCount + ' entries';

        //     const emptyState = document.getElementById('dynamicEmptyState');
        //     console.log(emptyState, visibleCount);

        //     if (emptyState) {
        //         if (visibleCount === 0) {
        //             emptyState.classList.remove('hidden');
        //         } else {
        //             emptyState.classList.add('hidden');
        //         }
        //     }
        // }

        function toggleAutoScroll() {
            autoScroll = !autoScroll;
            const btn = document.getElementById('autoScrollBtn');

            if (autoScroll) {
                btn.classList.add('active');
                btn.innerHTML = '<i data-lucide="arrow-down"></i><span>Auto-scroll: ON</span>';
                scrollToBottom();
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i data-lucide="arrow-down"></i><span>Auto-scroll: OFF</span>';
                scrollToTop();
            }

            // Re-initialize Lucide icons after changing HTML
            lucide.createIcons();
        }

        function scrollToTop() {
            const logContent = document.getElementById('logContent');
            logContent.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function scrollToBottom() {
            const logContent = document.getElementById('logContent');
            logContent.scrollTo({
                top: logContent.scrollHeight,
                behavior: 'smooth'
            });
        }

        // Auto-scroll when new content loads
        window.addEventListener('load', function() {
            if (autoScroll) {
                scrollToBottom();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }

            // Ctrl/Cmd + R to refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                location.reload();
            }
        });

        // Update visible count on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterLogs();
        });

        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        function showGenerateModal() {
            document.getElementById('generateModal').style.display = 'block';
            document.getElementById('filename').value = 'test-' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') +
                '.log';

            // Re-initialize icons in modal
            setTimeout(() => lucide.createIcons(), 50);
        }

        function confirmClearLog() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently clear all logs in this file!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff6b6b',
                cancelButtonColor: '#333',
                confirmButtonText: 'Yes, clear it!',
                cancelButtonText: 'Cancel',
                background: '#1e1e1e',
                color: '#e0e0e0',
                iconColor: '#ffd93d',
                customClass: {
                    popup: 'swal-dark-popup',
                    confirmButton: 'swal-confirm-btn',
                    cancelButton: 'swal-cancel-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading while clearing
                    Swal.fire({
                        title: 'Clearing logs...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        background: '#1e1e1e',
                        color: '#e0e0e0',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the form
                    document.getElementById('clearLogForm').submit();
                }
            });
        }

        function closeAlert(button) {
            const alert = button.closest('.alert');
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                alert.remove();
            }, 500);
        }

        function filterLogs() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const entries = document.querySelectorAll('.log-entry');
            let visibleCount = 0;

            entries.forEach(entry => {
                const level = entry.dataset.level;
                const message = entry.querySelector('.log-message');
                const originalText = entry.dataset.originalText || message.textContent;

                // Store original text
                if (!entry.dataset.originalText) {
                    entry.dataset.originalText = originalText;
                }

                let levelMatch = (activeFilter === 'all' || level === activeFilter);
                let searchMatch = (searchTerm === '' || originalText.toLowerCase().includes(searchTerm));

                if (levelMatch && searchMatch) {
                    // Highlight search term
                    if (searchTerm !== '') {
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        message.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                    } else {
                        message.textContent = originalText;
                    }

                    entry.classList.remove('hidden');
                    visibleCount++;
                } else {
                    entry.classList.add('hidden');
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount + ' entries';

            const emptyState = document.getElementById('dynamicEmptyState');

            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }

        // Auto-close alerts with fade out animation
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');

            alerts.forEach(alert => {
                // Start fade out after 5 seconds
                setTimeout(() => {
                    alert.style.transition = 'all 0.5s ease-out';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';

                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });

        function uploadLogFile(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('logFile', input.files[0]);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                Swal.fire({
                    title: 'Uploading...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    background: '#1e1e1e',
                    color: '#e0e0e0',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route('loglens.upload') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route('loglens.index') }}?file=' + encodeURIComponent(data
                                .filename);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to upload log file',
                            icon: 'error',
                            background: '#1e1e1e',
                            color: '#e0e0e0'
                        });
                    });
            }
        }
    </script>
</body>

</html>
