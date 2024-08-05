<?php 
    include "header.php"; 
    $keyNames = array_keys($result[0]);
?>

<main>
    <a href=""><button class="btn btn-primary">create new+</button></a>
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
            <td><a href=""><button class="btn btn-info">update</button></a></td>
            <td><a href=""><button class="btn btn-danger">delete</button></a></td>
        </tr>
        <?php } ?>
    </table>
</main>

<?php include "footer.php"; ?>