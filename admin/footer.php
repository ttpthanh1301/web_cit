        </div>
    </main>
</div>
<script src="<?= e(versioned_asset('../assets/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<?php foreach (($adminScripts ?? []) as $script): ?>
    <script src="<?= e(versioned_asset((string) $script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
