<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/web-tool/assets/styles/style.css" />
    <meta name="keywords" content="WFH, OFF, LATE" />

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
    <title>WFH, OFF & LATE</title>
</head>
<body>
<div id="table-container">
    <div class="table-filter">
        <!-- FROM_DATE PICKER -->
        <div class="date-picker">
            <p class="date-picker__label">Từ ngày <span>*</span></p>
            <div class="date-picker__box">
                <input
                    type="text"
                    id="calendar-from-date"
                    class="date-picker__input"
                    readonly
                    value="2024-02-01"
                />
                <img
                    src="/web-tool/assets/icons/calendar-svgrepo-com.svg"
                    alt="calendar icon"
                    class="calendar-icon"
                />
            </div>
        </div>
    </div>

    <!-- DATA TABLE -->
    <table id="table">

        <!-- TABLE HEADER -->
        <thead>
        <th class="head-cell">#</th>
        <th class="head-cell">Mã NV</th>
        <th class="head-cell">Nhân viên</th>
        <th class="head-cell">Vị trí công việc</th>
        <th class="head-cell">Loại</th>
        <th class="head-cell">Ngày gửi/Giờ checkin</th>
        <th class="head-cell">Trạng thái</th>
        <th class="head-cell">Lý do</th>
        <th class="head-cell">Phạt?</th>
        </thead>

        <tbody>
        @foreach($dataWFH as $key => $item)
            <tr class="row">
                <td>
                    <div class="cell cell--right">{{ $key + 1 }}</div>
                </td>
                <td>
                    <div class="cell">{{ $item['userStaffCode'] }}</div>
                </td>
                <td>
                    <div class="cell cell--left">
                        <div class="text-limit reason">{{$item['username']}}</div>
                    </div>
                </td>
                <td>
                    <div class="cell cell--left">{{$item['positionName']}}</div>
                </td>
                <td>
                    <div class="cell cell--left">Nghỉ phép</div>
                </td>
                <td>
                    <div class="cell text-red">{{ $item['createdAt'] }}</div>
                </td>
                <td>
                    <div class="cell cell--left">
                        <div class="text-limit reason">{{ $item['reason'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="cell">
                        <p class="status status--approved">{{ $item['statusApproval'] }}</p>
                    </div>
                </td>
                <td>
                    <div class="cell text-red">Có</div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
<script src="/web-tool/script.js"></script>
</body>
</html>
