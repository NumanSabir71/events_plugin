/**
 * Mae Admin Artists – repeater + media uploader
 * Vanilla JS, no jQuery dependency (wp.media is loaded separately).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        var list   = document.getElementById('mae-artists-list');
        var addBtn = document.getElementById('mae-ar-add');
        var tmpl   = document.getElementById('mae-ar-tmpl');

        if (!list || !addBtn || !tmpl) return;

        // Current row count (used as next index when adding)
        var rowCount = list.querySelectorAll('.mae-ar-row').length;

        /* ── Helpers ───────────────────────────────────────────── */

        function renumberHeaders() {
            var rows = list.querySelectorAll('.mae-ar-row');
            rows.forEach(function (row, idx) {
                var lbl = row.querySelector('.mae-ar-row__header strong');
                if (lbl) lbl.textContent = 'Artist ' + (idx + 1);
            });
        }

        function getHiddenInput(index) {
            return list.querySelector('input[name="mae_artists[' + index + '][art_pieces]"]');
        }

        function getThumbsContainer(index) {
            return list.querySelector('.mae-ar-thumbs[data-index="' + index + '"]');
        }

        function buildThumb(id, url, artistIndex, caption, price, description, dimensions) {
            var wrap = document.createElement('div');
            wrap.className = 'mae-ar-thumb';
            wrap.setAttribute('data-id', id);

            var img = document.createElement('img');
            img.src = url;
            img.alt = '';

            var rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'mae-ar-thumb__rm';
            rm.setAttribute('data-id', id);
            rm.innerHTML = '&times;';

            var cap = document.createElement('input');
            cap.type = 'text';
            cap.className = 'mae-ar-caption-input';
            cap.placeholder = 'Image caption...';
            cap.name = 'mae_artists[' + artistIndex + '][art_captions][' + id + ']';
            cap.value = caption || '';

            var desc = document.createElement('textarea');
            desc.className = 'mae-ar-desc-input';
            desc.rows = 2;
            desc.placeholder = 'Art description...';
            desc.name = 'mae_artists[' + artistIndex + '][art_descriptions][' + id + ']';
            desc.value = description || '';

            var dim = document.createElement('input');
            dim.type = 'text';
            dim.className = 'mae-ar-dim-input';
            dim.placeholder = 'Dimensions e.g. 24 × 36 in';
            dim.name = 'mae_artists[' + artistIndex + '][art_dimensions][' + id + ']';
            dim.value = dimensions || '';

            var pr = document.createElement('input');
            pr.type = 'number';
            pr.className = 'mae-ar-price-input';
            pr.placeholder = 'Price (USD)';
            pr.name = 'mae_artists[' + artistIndex + '][art_prices][' + id + ']';
            pr.step = '0.01';
            pr.min = '0';
            pr.value = price || '';

            wrap.appendChild(img);
            wrap.appendChild(rm);
            wrap.appendChild(cap);
            wrap.appendChild(desc);
            wrap.appendChild(dim);
            wrap.appendChild(pr);
            return wrap;
        }

        function readIds(index) {
            var hidden = getHiddenInput(index);
            if (!hidden || !hidden.value.trim()) return [];
            return hidden.value.split(',').map(function (s) { return parseInt(s, 10); }).filter(Boolean);
        }

        function writeIds(index, ids) {
            var hidden = getHiddenInput(index);
            if (hidden) hidden.value = ids.join(',');
        }

        /* ── A. Add artist row ─────────────────────────────────── */

        addBtn.addEventListener('click', function () {
            var html = tmpl.innerHTML;

            // Replace INDEX_LABEL (human display number) and INDEX (array key)
            var label = rowCount + 1;
            html = html.split('INDEX_LABEL').join(label);
            html = html.split('INDEX').join(rowCount);

            var tmp = document.createElement('div');
            tmp.innerHTML = html.trim();
            var newRow = tmp.firstElementChild;

            list.appendChild(newRow);
            rowCount++;
            renumberHeaders();
        });

        /* ── B. Remove artist row ──────────────────────────────── */

        list.addEventListener('click', function (e) {
            var rmBtn = e.target.closest('.mae-ar-remove');
            if (!rmBtn) return;
            var row = rmBtn.closest('.mae-ar-row');
            if (row) {
                row.remove();
                renumberHeaders();
            }
        });

        /* ── C. Media uploader ─────────────────────────────────── */

        // One frame per index so selections persist if re-opened
        var frames = {};

        list.addEventListener('click', function (e) {
            var mediaBtn = e.target.closest('.mae-ar-media');
            if (!mediaBtn) return;

            var index = mediaBtn.getAttribute('data-index');

            if (frames[index]) {
                frames[index].open();
                return;
            }

            var frame = wp.media({
                title:    'Select Art Pieces',
                multiple: true,
                library:  { type: 'image' },
                button:   { text: 'Add to Event' }
            });

            frame.on('open', function () {
                // Pre-select already chosen attachments
                var selection = frame.state().get('selection');
                readIds(index).forEach(function (id) {
                    var attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            frame.on('select', function () {
                var attachments = frame.state().get('selection').toJSON();
                var thumbsWrap  = getThumbsContainer(index);

                // Preserve captions, descriptions, dimensions and prices already typed before clearing
                var existingCaptions = {};
                var existingPrices   = {};
                var existingDescs    = {};
                var existingDims     = {};
                thumbsWrap.querySelectorAll('.mae-ar-thumb').forEach(function (t) {
                    var tid  = t.getAttribute('data-id');
                    var cap  = t.querySelector('.mae-ar-caption-input');
                    var pr   = t.querySelector('.mae-ar-price-input');
                    var desc = t.querySelector('.mae-ar-desc-input');
                    var dim  = t.querySelector('.mae-ar-dim-input');
                    if (tid && cap)  existingCaptions[tid] = cap.value;
                    if (tid && pr)   existingPrices[tid]   = pr.value;
                    if (tid && desc) existingDescs[tid]    = desc.value;
                    if (tid && dim)  existingDims[tid]     = dim.value;
                });

                var newIds = attachments.map(function (a) { return a.id; });

                thumbsWrap.innerHTML = '';
                newIds.forEach(function (id, i) {
                    var url = (attachments[i].sizes && attachments[i].sizes.thumbnail)
                        ? attachments[i].sizes.thumbnail.url
                        : attachments[i].url;
                    var caption     = existingCaptions[String(id)] || '';
                    var price       = existingPrices[String(id)]   || '';
                    var description = existingDescs[String(id)]    || '';
                    var dimensions  = existingDims[String(id)]     || '';
                    thumbsWrap.appendChild(buildThumb(id, url, index, caption, price, description, dimensions));
                });

                writeIds(index, newIds);
            });

            frames[index] = frame;
            frame.open();
        });

        /* ── D. Artist photo upload ────────────────────────────── */

        var photoFrames = {};

        list.addEventListener('click', function (e) {
            var photoBtn = e.target.closest('.mae-ar-photo-select');
            if (!photoBtn) return;

            var index = photoBtn.getAttribute('data-index');

            if (photoFrames[index]) {
                photoFrames[index].open();
                return;
            }

            var frame = wp.media({
                title:    'Select Artist Photo',
                multiple: false,
                library:  { type: 'image' },
                button:   { text: 'Use this photo' }
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var preview    = list.querySelector('.mae-ar-photo-preview[data-index="' + index + '"]');
                var hidden     = list.querySelector('input[name="mae_artists[' + index + '][photo_id]"]');
                var removeBtn  = list.querySelector('.mae-ar-photo-remove[data-index="' + index + '"]');

                var url = (attachment.sizes && attachment.sizes.thumbnail)
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                if (preview) preview.innerHTML = '<img src="' + url + '" alt="" class="mae-ar-photo-img">';
                if (hidden)  hidden.value = attachment.id;
                if (removeBtn) removeBtn.style.display = '';
                photoBtn.textContent = 'Change Photo';
            });

            photoFrames[index] = frame;
            frame.open();
        });

        list.addEventListener('click', function (e) {
            var rmPhoto = e.target.closest('.mae-ar-photo-remove');
            if (!rmPhoto) return;

            var index     = rmPhoto.getAttribute('data-index');
            var preview   = list.querySelector('.mae-ar-photo-preview[data-index="' + index + '"]');
            var hidden    = list.querySelector('input[name="mae_artists[' + index + '][photo_id]"]');
            var selectBtn = list.querySelector('.mae-ar-photo-select[data-index="' + index + '"]');

            if (preview)   preview.innerHTML = '';
            if (hidden)    hidden.value = '';
            rmPhoto.style.display = 'none';
            if (selectBtn) selectBtn.textContent = 'Select Photo';
        });

        /* ── F. Remove individual art-piece thumbnail ──────────── */

        list.addEventListener('click', function (e) {
            var rmThumb = e.target.closest('.mae-ar-thumb__rm');
            if (!rmThumb) return;

            var thumb     = rmThumb.closest('.mae-ar-thumb');
            var thumbsDiv = rmThumb.closest('.mae-ar-thumbs');
            var removeId  = parseInt(rmThumb.getAttribute('data-id'), 10);
            var index     = thumbsDiv ? thumbsDiv.getAttribute('data-index') : null;

            if (thumb) thumb.remove();

            if (index !== null) {
                var ids = readIds(index).filter(function (id) { return id !== removeId; });
                writeIds(index, ids);
            }
        });

    });

}());
