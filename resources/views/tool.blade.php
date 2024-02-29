@php
$defaultTime = new DateTime('08:00:00');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/web-tool/assets/styles/style.css" />
    <meta name="keywords" content="Working time management" />

    <!-- DATE PICKER LIBRARY -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
    />

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet"
    />
    <title>Working time management</title>
    <link rel="icon" type="image/x-icon" href="/web-tool/favicon.png"/>
</head>
<body>
    <div id="table-container">
        <div class="table-filter">
            <!-- FROM_DATE PICKER -->
            <div class="date-picker">
                <p class="date-picker__label">Ngày <span>*</span></p>
                <div class="date-picker__box">
                    <input
                        type="text"
                        id="calendar-from-date"
                        class="date-picker__input"
                        value="{{ request()->input('calendar-from-date') }}"
                    />
                    <img
                        src="/web-tool/assets/icons/calendar-svgrepo-com.svg"
                        alt="calendar icon"
                        class="calendar-icon"
                    />
                </div>
            </div>
        </div>

        <table id="table">
            <thead>
            <th class="head-cell">#</th>
            <th class="head-cell">Mã NV</th>
            <th class="head-cell">Nhân viên</th>
            <th class="head-cell">Vị trí công việc</th>
            <th class="head-cell">Loại</th>
            <th class="head-cell">Ngày gửi</th>
            <th class="head-cell">Giờ checkin</th>
            <th class="head-cell">Trạng thái</th>
            <th class="head-cell">Lý do</th>
            <th class="head-cell">Phạt?</th>
            </thead>
            <tbody>
            @foreach($dataStaff as $key => $item)
                <tr class="row">
                    <td>
                        <div class="cell cell--left">{{ $key + 1 }}</div>
                    </td>
                    <td>
                        <div class="cell cell--left">{{ $item['staffCode'] }}</div>
                    </td>
                    <td>
                        <div class="cell cell--left">
                            <div class="text-limit reason">{{$item['staffName']}}</div>
                        </div>
                    </td>
                    <td>
                        <div class="cell cell--left">{{$item['staffPosition']}}</div>
                    </td>
                    <td>
                        <div class="cell cell--left">{{ $item['requestType'] }}</div>
                    </td>
                    <td>
                        <div class="cell cell--left {{ $item['isViolateCreatedAt'] ? 'text-red' : '' }}">{{ $item['requestCreatedAt'] ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="cell cell--left {{ $item['isViolatetimeCheckIn'] ? 'text-red' : '' }}">{{ $item['timeCheckIn'] ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="cell cell--left">
                            @if(!empty($item['displayStatus']))
                            <p class="status {{ $item['requestStatus'] === 'APPROVED' ? 'status--approved' : 'status--waiting'}}">{{ $item['displayStatus'] }}</p>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="cell cell--left">
                            <div class="text-limit reason">{{ $item['requestReason'] }}</div>
                        </div>
                    </td>
                    <td>
                        @if($item['isViolateCreatedAt'] || $item['isViolatetimeCheckIn'])
                            <div class="cell cell--left text-red" style="font-size: 20px">Có</div>
                        @else
                            <div class="cell cell--left">Không</div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
    <script src="/web-tool/script.js"></script>
    <script>
        function updateUrlParameters() {
            const selectBox1Value = document.getElementById('calendar-from-date').value;
            const currentUrl = window.location.href;
            const urlParams = new URLSearchParams(window.location.search);

            urlParams.set('calendar-from-date', selectBox1Value);
            window.location.href = currentUrl.split('?')[0] + '?' + urlParams.toString();
        }

        document.getElementById('calendar-from-date').addEventListener('change', updateUrlParameters);
    </script>
</body>
</html>
