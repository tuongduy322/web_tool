@php
    $defaultTime = new DateTime(request()->input('calendar-from-date'));
    $showTime = new DateTime();
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

            <div class="date-picker" style="width: 350px">
                <p class="date-picker__label">Phạt?</p>
                <div class="group-select-box">
                    <div class="container css-z40azo-control" id="container-select">
                        <div class="group1" onclick="toggleDropdown()">
                            <div class="label-container" style="width: 70px">
                                <span id="selected-item-label" class="{{ request()->input('filter', 'Tất cả') === 'Có' ? 'txt-red' : '' }}">{{ request()->input('filter', 'Tất cả') }}</span>
                            </div>
                        </div>
                        <div class="group2">
                            <div onclick="toggleDropdown()" class="dropdown-btn css-tlfecz-indicatorContainer" aria-hidden="true"><svg height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false" class="css-8mmkcg"><path d="M4.516 7.548c0.436-0.446 1.043-0.481 1.576 0l3.908 3.747 3.908-3.747c0.533-0.481 1.141-0.446 1.574 0 0.436 0.445 0.408 1.197 0 1.615-0.406 0.418-4.695 4.502-4.695 4.502-0.217 0.223-0.502 0.335-0.787 0.335s-0.57-0.112-0.789-0.335c0 0-4.287-4.084-4.695-4.502s-0.436-1.17 0-1.615z"></path></svg></div>
                            <span class="css-1okebmr-indicatorSeparator"></span>
                            <div class="css-tlfecz-indicatorContainer" id="clear-button" onclick="clearSelection()" aria-hidden="true"><svg height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false" class="css-8mmkcg"><path d="M14.348 14.849c-0.469 0.469-1.229 0.469-1.697 0l-2.651-3.030-2.651 3.029c-0.469 0.469-1.229 0.469-1.697 0-0.469-0.469-0.469-1.229 0-1.697l2.758-3.15-2.759-3.152c-0.469-0.469-0.469-1.228 0-1.697s1.228-0.469 1.697 0l2.652 3.031 2.651-3.031c0.469-0.469 1.228-0.469 1.697 0s0.469 1.229 0 1.697l-2.758 3.152 2.758 3.15c0.469 0.469 0.469 1.229 0 1.698z"></path></svg></div>
                            <div class="dropdown-content" id="dropdown-content">
                                <div class="dropdown-item" onclick="selectItem('-')">-</div>
                                <div class="dropdown-item" onclick="selectItem('Có')" style="color: red">Có</div>
                            </div>
                        </div>
                    </div>
                    <div class="count-filter">
                        <p id="label-punished">Phạt: <span>{{ $countPunished }}</span></p>
                    </div>
                    <div class="count-filter">
                        <p id="label-not-punished">OK: <span>{{ $countNotPunished }}</span></p>
                    </div>
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
            <th class="head-cell">Từ ngày</th>
            <th class="head-cell">Đến ngày</th>
            <th class="head-cell">Giờ checkin</th>
            <th class="head-cell">Trạng thái</th>
            <th class="head-cell">Lý do</th>
            <th class="head-cell">Phạt? (10AM)</th>
            </thead>
            <tbody>
            @php
                $ind = 0;
            @endphp
            @foreach($dataStaff as $key => $item)
                <tr class="row">
                    <td>
                        <div class="cell cell--left">{{ ++$ind }}</div>
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
                        @if (!empty($item['requestCreatedAt']))
                            @php
                                $date = $time = '';
                                $requestCreatedAt = $item['requestCreatedAt'] ?? [];
                                if (!empty($requestCreatedAt)) {
                                    list($date, $time) = explode(' ', $requestCreatedAt);
                                }
                            @endphp
                            <div class="cell cell--left cell-date {{ $item['isViolateCreatedAt'] ? 'text-red' : '' }}">
                                <span>{{ $date ?? '' }}</span>
                                <span>{{ $time ?? '' }}</span>
                            </div>
                        @else
                            <div class="cell cell--left {{ $item['isViolateCreatedAt'] ? 'text-red' : '' }}"></div>
                        @endif
                    </td>
                    <td>
                        @if (!empty($item['fromDate']))
                            @php
                                $date = $time = '';
                                $fromDate = $item['fromDate'] ?? '';
                                if (!empty($fromDate)) {
                                    $arrFromDate = explode(' ', $fromDate);
                                    if (count($arrFromDate) > 1) {
                                        list($date, $time) = explode(' ', $fromDate);
                                    } else {
                                        list($date) = explode(' ', $fromDate);
                                        $time = '08:00:00';
                                    }
                                }
                            @endphp
                            <div class="cell cell--left cell-date">
                                <span>{{ $date ?? '' }}</span>
                                <span>{{ $time ?? '' }}</span>
                            </div>
                        @else
                            <div class="cell cell--left"></div>
                        @endif
                    </td>
                    <td>
                        @if (!empty($item['endDate']))
                            @php
                                $date = $time = '';
                                $endDate = $item['endDate'] ?? '';
                                if (!empty($endDate)) {
                                    $arrEndDate = explode(' ', $endDate);
                                    if (count($arrEndDate) > 1) {
                                        list($date, $time) = explode(' ', $endDate);
                                    } else {
                                        list($date) = explode(' ', $endDate);
                                        $time = '17:00:00';
                                    }
                                    list($date) = explode(' ', $endDate);
                                }
                            @endphp
                            <div class="cell cell--left cell-date">
                                <span>{{ $date ?? '' }}</span>
                                <span>{{ $time ?? '' }}</span>
                            </div>
                        @else
                            <div class="cell cell--left"></div>
                        @endif
                    </td>
                    <td>
                        @if (!empty($item['timeCheckIn']))
                            @php
                                $date = $time = '';
                                $requestCreatedAt = $item['timeCheckIn'] ?? [];
                                if (!empty($requestCreatedAt)) {
                                    list($dateCheckIn, $timeCheckIn) = explode(' ', $requestCreatedAt);
                                }
                            @endphp
                            <div class="cell cell--left cell-date {{ $item['isViolatetimeCheckIn'] ? 'text-red' : '' }}">
                                <span>{{ $dateCheckIn ?? '' }}</span>
                                <span>{{ $timeCheckIn ?? '' }}</span>
                            </div>
                        @else
                            <div class="cell cell--left {{ $item['isViolatetimeCheckIn'] ? 'text-red' : '' }}">N/A</div>
                        @endif
                    </td>
                    <td>
                        <div class="cell cell--left">
                            @if(!empty($item['displayStatus']))
                            <p class="status {{ $item['requestStatus'] === 'APPROVED' ? 'status--approved' : 'status--waiting'}}">{{ $item['displayStatus'] }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="tooltip">
                        <div class="cell cell--left">
                            <div class="text-limit reason">
                                {{ $item['requestReason'] }}
                            @if(!empty($item['requestReason']))
                                <span class="tooltiptext">{{ $item['requestReason'] }}</span>
                            @endif
                            </div>
                        </div>
                    </td>
                    <td>
                    @if($defaultTime->format('Y-m-d') >= $showTime->format('Y-m-d') && \Carbon\Carbon::now()->setTimezone('Asia/Ho_Chi_Minh')->format('H:i:s') < '10:00:00')
                        <div class="cell cell--left"></div>
                    @else
                        @if($item['isViolateCreatedAt'] || $item['isViolatetimeCheckIn'])
                        <div class="cell cell--left text-red">Có</div>
                        @else
                        <div class="cell cell--left">-</div>
                        @endif
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
