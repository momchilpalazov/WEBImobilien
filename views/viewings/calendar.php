<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Календар с огледи</h1>
        <a href="/viewings/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Нов оглед
        </a>
    </div>

    <!-- Филтри -->
    <div class="card my-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Агент</label>
                    <select name="agent_id" class="form-select">
                        <option value="">Всички агенти</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id'] ?>" 
                                    <?= ($filters['agent_id'] ?? '') == $agent['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($agent['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">От дата</label>
                    <input type="date" name="start" class="form-control" 
                           value="<?= $filters['start'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">До дата</label>
                    <input type="date" name="end" class="form-control" 
                           value="<?= $filters['end'] ?? '' ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Приложи
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Календар -->
    <div class="card mb-4">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детайли за огледа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Имот:</strong>
                    <span id="eventProperty"></span>
                </div>
                <div class="mb-3">
                    <strong>Клиент:</strong>
                    <span id="eventClient"></span>
                </div>
                <div class="mb-3">
                    <strong>Агент:</strong>
                    <span id="eventAgent"></span>
                </div>
                <div class="mb-3">
                    <strong>Дата и час:</strong>
                    <span id="eventDateTime"></span>
                </div>
                <div class="mb-3">
                    <strong>Статус:</strong>
                    <span id="eventStatus"></span>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="eventViewLink" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Преглед
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet" />

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['dayGrid', 'timeGrid', 'interaction'],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'bg',
        firstDay: 1,
        buttonText: {
            today: 'Днес',
            month: 'Месец',
            week: 'Седмица',
            day: 'Ден'
        },
        defaultView: 'timeGridWeek',
        navLinks: true,
        selectable: true,
        selectMirror: true,
        select: function(arg) {
            window.location.href = '/viewings/create?scheduled_at=' + arg.startStr;
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        events: function(info, successCallback, failureCallback) {
            // Fetch events via AJAX
            fetch('/viewings/calendar?' + new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                agent_id: '<?= $filters['agent_id'] ?? '' ?>'
            }), {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                successCallback(data);
            })
            .catch(error => {
                failureCallback(error);
            });
        }
    });

    calendar.render();
});

function showEventDetails(event) {
    document.getElementById('eventProperty').textContent = event.title;
    document.getElementById('eventClient').textContent = event.extendedProps.client_name;
    document.getElementById('eventAgent').textContent = event.extendedProps.agent_name;
    document.getElementById('eventDateTime').textContent = event.start.toLocaleString();
    document.getElementById('eventStatus').textContent = event.extendedProps.status;
    document.getElementById('eventViewLink').href = '/viewings/view/' + event.id;

    var modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}
</script>

<style>
#calendar {
    height: 800px;
}

.fc-event {
    cursor: pointer;
}

.fc-event.bg-primary {
    background-color: var(--bs-primary) !important;
    border-color: var(--bs-primary) !important;
}

.fc-event.bg-success {
    background-color: var(--bs-success) !important;
    border-color: var(--bs-success) !important;
}

.fc-event.bg-danger {
    background-color: var(--bs-danger) !important;
    border-color: var(--bs-danger) !important;
}

.fc-event.bg-warning {
    background-color: var(--bs-warning) !important;
    border-color: var(--bs-warning) !important;
}
</style>

<?php require_once 'views/layout/footer.php'; ?> 