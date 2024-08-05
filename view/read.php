<?php include "header.php"; ?>

<main>
    <a href=<?php echo "../controller/index.php?action=master_create&data=" . $data; ?>><button class="btn btn-primary">create new+</button></a>
    <table>
        <tr>
        <?php foreach($keyNames as $key){ ?>
            <th><?php echo $key; ?></th>
        <?php } ?>
        <th colspan="2">actions</th>
        </tr>
        <?php foreach($result as $key){ ?>
        <tr>
            <?php foreach($keyNames as $name){ ?>
                <td><?php echo $key[$name]; ?></td>
            <?php } ?>
            <td><a href=<?php echo "../controller/index.php?action=master_update&data=" . $data . "&code=" . $key[$keyNames[0]]; ?>><button class="btn btn-info">update</button></a></td>
            <td><a href=<?php echo "../controller/index.php?action=master_delete&data=" . $data . "&code=" . $key[$keyNames[0]]; ?>><button class="btn btn-danger">delete</button></a></td>
        </tr>
        <?php } ?>
    </table>
</main>

<?php include "footer.php"; ?>