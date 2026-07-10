        </div>
    </main>
</div>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php foreach (($adminScripts ?? []) as $script): ?>
    <script src="<?= e((string) $script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
