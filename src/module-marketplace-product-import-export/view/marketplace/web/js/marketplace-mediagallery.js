define(['jquery', 'mage/url', 'mage/translate'], function ($, url, $t) {
    'use strict';

    var cfg      = window.CM_MG_CONFIG || {};
    var urls     = cfg.urls || {};
    var sellerId = cfg.sellerId || 0;

    var currentFolder = ''; // keep track for filters

    /* ---------- IMAGES ---------- */
    function renderImages(images) {
        const grid = $('#cm_images_grid').empty();

        if (!images || !images.length) {
            grid.append($('<div class="no-images">').text($.mage.__('No images found.')));
            return;
        }

        images.forEach(function (img) {
            const filename  = img.title || img.path.split('/').pop();
            const extension = filename.split('.').pop().toUpperCase();

            const realImg = new Image();
            realImg.src = img.url;

            realImg.onload = function () {
                const dimensions = `${realImg.naturalWidth}×${realImg.naturalHeight}px`;
                const info = `
                    <div class="cm-image-info">
                        <div class="cm-image-name" title="${filename}">${filename}</div>
                        <div class="cm-image-meta">${extension} • ${dimensions}</div>
                    </div>
                `;

                const thumb = $(`
                    <div class="cm-thumb admin__media-thumbnail" tabindex="0" style="cursor:pointer;">
                        <label class="admin__media-thumb-label">
                            <span class="admin__media-thumb-wrap">
                                <img src="${img.url}" alt="${filename}" class="admin__control-thumbnail">
                            </span>
                            <input type="checkbox"
                                class="cm-image-check admin__control-checkbox"
                                value="${img.entity_id}">
                        </label>
                        ${info}
                    </div>
                `);

                thumb.on('click keydown', function (e) {
                    if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
                        const $checkbox = $(this).find('.cm-image-check');
                        const checked   = !$checkbox.prop('checked');
                        $checkbox.prop('checked', checked).trigger('change');
                        $(this).toggleClass('selected', checked);
                    }
                });

                thumb.on('dblclick', function () {
                    $('.cm-image-check').prop('checked', false);
                    $('.cm-thumb').removeClass('selected');
                    $(this).find('.cm-image-check').prop('checked', true).trigger('change');
                    $(this).addClass('selected');
                });

                grid.append(thumb);
            };
        });
    }

    /* ---------- FOLDERS ---------- */
    function buildNested(paths) {
        const root = {};
        (paths || []).forEach(function (p) {
            const parts = p.split('/');
            let cur = root;
            let full = '';
            parts.forEach(function (seg) {
                full = full ? full + '/' + seg : seg;
                cur[seg] = cur[seg] || { __path: full, __children: {} };
                cur = cur[seg].__children;
            });
        });
        function toArr(obj) {
            return Object.keys(obj).map(k => ({
                name: k,
                path: obj[k].__path,
                children: toArr(obj[k].__children)
            }));
        }
        return toArr(root);
    }

    function buildTree(data) {
        var $ul = $('<ul>');
        (data || []).forEach(function (node) {
            var $li = $('<li>').data('path', node.path);
            var $toggle = $('<span class="toggle">')
                .text(node.children && node.children.length ? '▶' : '');
            var $name = $('<span class="folder-name">').text(node.name);

            $li.append($toggle).append($name);

            if (node.children && node.children.length) {
                var $childUl = buildTree(node.children).hide();
                $li.append($childUl);
                $toggle.on('click', function (e) {
                    e.stopPropagation();
                    $childUl.toggle();
                    $toggle.text($childUl.is(':visible') ? '▼' : '▶');
                });
            }

            $name.on('click', function () {
                $('.cm-folder-tree li').removeClass('active');
                $li.addClass('active');
                currentFolder = node.path;
                loadImages(currentFolder);
            });

            $ul.append($li);
        });
        return $ul;
    }

    function loadFolders() {
        $.getJSON(urls.list_folders, function (res) {
            if (!res || !res.success) {
                $('#cm_folder_tree').html('<div>' + (res && res.message ? res.message : $t('No folders')) + '</div>');
                return;
            }
            var treeData = buildNested(res.folders);
            var $tree = buildTree(treeData);
            $('#cm_folder_tree').empty().append($tree);

            // auto select first folder
            var $first = $('#cm_folder_tree li').first();
            if ($first.length) {
                $first.addClass('active');
                currentFolder = $first.data('path');
                loadImages(currentFolder);
            }
        });
    }

    function loadCategories() {
        // expects {success:true, categories:[{id:1,name:'Cat1'},...]}
        $.getJSON(urls.list_categories, function (res) {
            if (res && res.success && Array.isArray(res.categories)) {
                var $sel = $('#cm_filter_category');
                res.categories.forEach(function (c) {
                    $('<option>').val(c.id).text(c.name).appendTo($sel);
                });
            }
        });
    }

    function loadImages(folder) {
        if (!folder) return;
        const nameFilter = $('#cm_filter_name').val();
        const catFilter  = $('#cm_filter_category').val();
        $.getJSON(urls.list_images, {
            folder: folder,
            name:   nameFilter,
            category: catFilter
        }, function (res) {
            if (!res.success) {
                $('#cm_images_grid').empty()
                    .append($('<div>').text(res.message || $t('No images')));
                return;
            }
            renderImages(res.images);
        });
    }

    /* ---------- READY ---------- */
    $(function () {
        loadFolders();
        loadCategories();

        $('#cm_filter_apply').on('click', function () {
            if (currentFolder) {
                loadImages(currentFolder);
            }
        });

        $('#cm_delete_images').on('click', function () {
            const ids = $('.cm-image-check:checked').map(function () {
                return $(this).val();
            }).get();
            if (!ids.length) { alert($t('Please select at least one image.')); return; }
            if (!confirm($t('Delete selected images?'))) return;

            $.ajax({
                url: cfg.deleteUrl,
                type: 'POST',
                data: { selected: ids },
                showLoader: true
            }).done(function () {
                if (currentFolder) loadImages(currentFolder);
            }).fail(function () {
                alert($t('Failed to delete images.'));
            });
        });

        $('#cm_create_folder').on('click', function () {
            var parent = $('.cm-folder-tree li.active').data('path') || ('seller/' + sellerId);
            var name   = prompt($t('Enter new folder name:'));
            if (!name) return;
            $.post(urls.create_folder, { name: name, parent: parent }, function (res) {
                if (!res.success) { alert(res.message || $t('Cannot create folder')); return; }
                loadFolders();
            }, 'json');
        });

        $('#cm_delete_folder').on('click', function () {
            var folder = $('.cm-folder-tree li.active').data('path');
            if (!folder || !confirm($t('Delete this folder and its files?'))) return;
            $.post(urls.delete_folder, { folder: folder }, function (res) {
                if (!res.success) { alert(res.message || $t('Delete failed')); return; }
                loadFolders();
                $('#cm_images_grid').empty();
            }, 'json');
        });

        $('#cm_upload_image').on('click', function () {
            if (!currentFolder) {
                alert($t('Please select a folder first.'));
                return;
            }
            var uploadUrl = cfg.uploadUrl + '?folder=' + encodeURIComponent(currentFolder);
            window.location.href = uploadUrl;
        });
    });
});
