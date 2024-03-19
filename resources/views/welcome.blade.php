<!DOCTYPE html>
<html>
<head>
    <title>Your HTML Export</title>
</head>
<body>
<div id="htmlContent" style="display: none;">
    <table id="data" >
@foreach($dataExport as $key => $item)
        <tr>
            <td style="text-align:center">{{ $item['month'] ?? '' }}</td>
            <td style="text-align:center">{{ $item['date'] ?? '' }}</td>
            <td colspan="6" style="text-align:left">{{ $item['staffName'] ?? '' }}</td>
            <td colspan="2" style="text-align:center">{{ in_array($item['staffTeam'], ['BE', 'Web Dept']) ? '10000' : ''}}</td>
            <td colspan="2" style="text-align:center">{{ in_array($item['staffTeam'], ['FE']) ? '10000' : ''}}</td>
            <td style="text-align:center">Đi trễ</td>
            <td style="text-align:center;background-color: yellow">Chưa thu</td>
        </tr>
@endforeach
    </table>
</div>
<button id="copyButton">Copy HTML Content</button>

<script>
    function copyHtmlToClipboard() {
        const htmlContent = document.getElementById('htmlContent').innerHTML;
        const tempTextArea = document.createElement('textarea');

        tempTextArea.value = htmlContent;
        document.body.appendChild(tempTextArea);
        tempTextArea.select();
        document.execCommand('copy');
        document.body.removeChild(tempTextArea);

        alert('HTML content copied to clipboard!');
    }

    document.getElementById('copyButton').addEventListener('click', copyHtmlToClipboard);
</script>
</body>
</html>
