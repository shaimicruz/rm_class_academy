<?php
// includes/footer.php
?>
    <button type="button" class="scroll-to-top" id="scrollToTopBtn" aria-label="Volver arriba">↑</button>

    <div class="modal" id="archivoModal" aria-hidden="true">
        <div class="modal-content" style="width:min(96vw,980px);max-height:92vh;overflow:auto;">
            <button type="button" class="close-btn" id="cerrarArchivoModal" aria-label="Cerrar">&times;</button>
            <div id="archivoModalBody"></div>
        </div>
    </div>

    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        (function () {
            const scrollBtn = document.getElementById('scrollToTopBtn');
            const modal = document.getElementById('archivoModal');
            const modalBody = document.getElementById('archivoModalBody');
            const closeModalBtn = document.getElementById('cerrarArchivoModal');

            if (scrollBtn) {
                const toggleScrollBtn = () => {
                    if (window.scrollY > 260) scrollBtn.classList.add('visible');
                    else scrollBtn.classList.remove('visible');
                };
                toggleScrollBtn();
                window.addEventListener('scroll', toggleScrollBtn);
                scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
            }

            const closeModal = () => {
                if (!modal || !modalBody) return;
                modal.classList.remove('activo');
                modal.setAttribute('aria-hidden', 'true');
                modalBody.innerHTML = '';
            };

            const buildViewer = (url, ext) => {
                const safeUrl = url.replace(/"/g, '&quot;');
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    return '<img src="' + safeUrl + '" alt="Vista previa" class="archivo-imagen">';
                }
                if (ext === 'pdf') {
                    return '<iframe src="' + safeUrl + '" class="archivo-embed" style="height:72vh;border:none;"></iframe>';
                }
                if (['mp4', 'webm', 'ogg'].includes(ext)) {
                    return '<video controls class="archivo-video"><source src="' + safeUrl + '"></video>';
                }
                return '<p class="text-muted">No hay vista previa para este archivo.</p>';
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('a.abrir-archivo-modal, a.archivo-evidencia, a.archivo-link[data-modal="1"]');
                if (!trigger || !modal || !modalBody) return;

                const href = trigger.getAttribute('href');
                if (!href) return;

                event.preventDefault();
                const cleanHref = href.split('?')[0].split('#')[0];
                const ext = cleanHref.includes('.') ? cleanHref.split('.').pop().toLowerCase() : '';

                modalBody.innerHTML = buildViewer(href, ext)
                    + '<div style="margin-top:14px;"><a class="btn-principal" href="' + href.replace(/"/g, '&quot;')
                    + '" download>Descargar archivo</a></div>';
                modal.classList.add('activo');
                modal.setAttribute('aria-hidden', 'false');
            });

            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (modal) {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal();
                });
            }
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });
        })();
    </script>
</body>
</html>
