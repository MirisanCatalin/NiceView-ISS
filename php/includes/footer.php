</main>

<footer>
    <p>NiceView &copy; 2026 - Proiect PHP Vanilla</p>
</footer>

<?php
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$js_prefix = ($current_dir === 'php') ? '' : '../';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo $js_prefix; ?>js/date.js"></script>
<script src="<?php echo $js_prefix; ?>js/tema.js"></script>
<script src="<?php echo $js_prefix; ?>js/tabel.js"></script>
<script src="<?php echo $js_prefix; ?>js/carousel.js"></script>
<script src="<?php echo $js_prefix; ?>js/liste.js"></script>

</body>
</html>