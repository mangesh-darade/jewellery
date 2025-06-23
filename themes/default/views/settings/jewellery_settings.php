<!DOCTYPE html>
<html>
<head>
    <title>Jewellery Daily Rates</title>
    <!-- Bootstrap CSS CDN -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/csx`s/bootstrap.min.css" rel="stylesheet"> -->

    <style>
        #msg {
            margin-top: 10px;
        }
    </style>
</head>
<body class="p-3">
<p id="msg" style="color: green;"></p>

<div class="table-responsive mt-3">
    <table id="rates-table" class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Metal Type</th>
                <th>Purity</th>
                <th>Unit</th>
                <th class="text-end">Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rates as $row): 
                ?>
                <tr>
                    <td><?php echo $row->metal_type; ?></td>
                    <td><?php echo $row->metal_purity ?: '-'; ?></td>
                    <td><?php echo $row->unit; ?></td>
                    <td class="text-end">
                        <input type="text" class="form-control rate-field text-end" data-id="<?php echo $row->id; ?>" value="<?php echo $this->sma->formatMoney($row->rate); ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<button id="save-btn" class="btn btn-primary mt-2">Save</button>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        // No checkbox to toggle, so table and button are visible by default

        $('#save-btn').on('click', function (e) {
            e.preventDefault();

            var rates = {};
            $('.rate-field').each(function () {
                var id = $(this).data('id');
                var rate = $(this).val();
               var cleanRate = rate.replace(/Rs\.?\s*/gi, '').replace(/[^0-9.]/g, '');
                rates[id] = cleanRate;
            });

            $.ajax({
                url: '<?php echo site_url("system_settings/jewellery_settings"); ?>',
                type: 'GET',
                data: { rates: rates },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        $('#msg').text(res.message).css('color', 'green');
                        
                    } else {
                        $('#msg').text('Update failed.').css('color', 'red');
                    }
                },
                error: function () {
                    $('#msg').text('Server error occurred.').css('color', 'red');
                }
            });
        });
    });
</script>

</body>
</html>
