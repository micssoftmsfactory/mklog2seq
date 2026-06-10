<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>mkLog2Seq HTML(test) version!</title>
    <style>
        :root {
            --bg: #f6f9f0;
            --card: #ffffff;
            --accent: #3f8f55;
            --accent-soft: #dcefdc;
            --border: #d8ddcf;
            --text: #333333;
            --muted: #6b6b6b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.7;
        }

        a {
            color: var(--accent);
        }

        .hero {
            background: linear-gradient(135deg, #f0ffe0, #d8f2ff);
            padding: 3rem 1.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .tagline {
            display: inline-flex;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.7);
            color: var(--accent);
            font-size: 0.9rem;
            letter-spacing: 0.05em;
        }

        .hero h1 {
            margin: 0.6rem 0;
            font-size: clamp(1.6rem, 4vw, 2.2rem);
        }

        .hero p {
            max-width: 760px;
            margin: 0 auto 1.5rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }

        .hero-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            border: 1px solid var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .hero-actions a.primary {
            background: var(--accent);
            color: #ffffff;
        }

        .hero-actions a:not(.primary) {
            background: transparent;
            color: var(--accent);
        }

        .page {
            width: min(1100px, 92%);
            margin: 0 auto;
            padding: 2.5rem 0 4rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 12px 35px rgba(60, 65, 55, 0.08);
        }

        .card h2,
        .card h3 {
            margin-top: 0;
            color: var(--accent);
        }

        .intro #msg {
            margin-bottom: 0.5rem;
        }

        .note-list {
            list-style: disc;
            padding-left: 1.25rem;
            background: #fdfef8;
            border-left: 4px solid var(--accent);
            margin: 1rem 0 0;
        }

        .note-list li {
            margin: 0.35rem 0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        label {
            font-weight: 600;
        }

        input,
        textarea {
            font: inherit;
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fbfcf7;
            resize: vertical;
        }

        textarea {
            min-height: 180px;
        }

        .helper {
            font-size: 0.85rem;
            color: var(--muted);
            margin: 0;
        }

        .helper code {
            background: var(--accent-soft);
            padding: 0.1rem 0.35rem;
            border-radius: 6px;
        }

        .textareas {
            display: grid;
            gap: 1.5rem;
        }

        .experimental {
            background: #f6fbff;
            border: 1px dashed #91b6ff;
            border-radius: 14px;
            padding: 1.5rem;
        }

        .submit-wrap {
            display: flex;
            justify-content: flex-end;
        }

        .submit-wrap input {
            width: fit-content;
            background: var(--accent);
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: opacity 0.2s ease;
        }

        .submit-wrap input:hover {
            opacity: 0.9;
        }

        figure.sample {
            margin: 0;
            text-align: center;
        }

        figure.sample img {
            width: min(620px, 100%);
            border-radius: 12px;
            border: 1px solid var(--border);
            display: block;
        }

        figure.sample figcaption {
            margin-top: 0.75rem;
            color: var(--muted);
        }

        .lightbox-target {
            cursor: zoom-in;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .lightbox-target:hover {
            transform: scale(1.015);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .image-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 9999;
        }

        .image-lightbox.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        .image-lightbox__dialog {
            width: min(900px, 90vw);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }

        .image-lightbox__dialog img {
            width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            background: #000;
        }

        .image-lightbox__caption {
            color: #fff;
            text-align: center;
            font-size: 0.95rem;
        }

        .image-lightbox__close {
            align-self: flex-end;
            border: none;
            background: rgba(0, 0, 0, 0.65);
            color: #fff;
            font-size: 1.35rem;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 999px;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .image-lightbox__close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .credit {
          background: #0f261c;
          color: #d1f4e1;
          text-align: center;
          padding: 1.5rem 0;
          margin-top: 2rem;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #121517;
                color: #e6ebf0;
            }

            .card {
                background: #1b2024;
                border-color: #2d343a;
                box-shadow: none;
            }

            input,
            textarea {
                background: #101417;
                color: inherit;
                border-color: #2d343a;
            }

            .hero {
                background: linear-gradient(135deg, #23312a, #1c2430);
                border-color: #2d343a;
            }

            .note-list {
                background: #1f261b;
            }

            footer {
                color: #a1aab5;
            }
        }
    </style>
</head>
<body>
    <header class="hero">
        <span class="tagline">ログを HTML シーケンス図へ</span>
        <h1>mkLog2Seq HTML (test) version!</h1>
        <p>
            プログラムのデバッグ用ログから開始・終了ログを抽出し、シーケンス図を HTML として自動生成します。
            ログフォーマットが一致すれば既存ログもそのまま利用できます。
        </p>
        <div class="hero-actions">
            <a class="primary" href="#form">ログ入力フォームへ</a>
            <a href="#sample">出力例を見る</a>
        </div>
    </header>
    <main class="page">
        <section class="card intro">
            <h2>使い方ガイド</h2>
            <p id="msg">
                【デフォルトログフォーマット】<br>
                ファイル名:行番号:関数名(引数) start<br>
                ファイル名:行番号:関数名 [return(戻り値) | return | end]
            </p>
            <ul class="note-list">
                <li>開始・終了が判別できるログであれば、ログフォーマットの指定のみで既存ログを利用できます。</li>
                <li>最低限、ログに関数名と開始/終了を判断する情報が含まれている必要があります。</li>
                <li>デフォルト入力のままでもサンプル出力を確認できます。</li>
                <li>ログサイズが大きい場合は処理時間がかかり、サーバー制限のタイムアウトになる可能性があります。</li>
            </ul>
        </section>

        <section id="form" class="card">
            <h2>ログ入力フォーム</h2>
            <form method="post" action="mklog2seq.php">
                <div class="grid-2">
                    <div class="form-row">
                        <label for="username">username</label>
                        <input type="text" id="username" name="username" value="guest">
                        <p class="helper">出力に付与されるユーザー名です。</p>
                    </div>
                    <div class="form-row">
                        <label for="title">title</label>
                        <input type="text" id="title" name="title" value="sample log">
                        <p class="helper">シーケンス図のタイトルを入力してください。</p>
                    </div>
                </div>

                <div class="textareas">
                    <div class="form-row">
                        <label for="log">log</label>
                        <p class="helper"><code>log.txt</code> の内容が自動で読み込まれます。</p>
                        <textarea id="log" name="log" rows="10">
<?php
    $log = file_get_contents("log.txt");
    echo $log;
?>
                        </textarea>
                    </div>

                    <div class="form-row">
                        <label for="header">header</label>
                        <p class="helper"><code>header.txt</code> で定義した内容を編集できます。</p>
                        <textarea id="header" name="header" rows="10">
<?php
    $header = file_get_contents("header.txt");
    echo $header;
?>
                        </textarea>
                    </div>

                    <div class="form-row">
                        <label for="footer">footer</label>
                        <p class="helper"><code>footer.txt</code> の内容です。</p>
                        <textarea id="footer" name="footer" rows="10">
<?php
    $footer = file_get_contents("footer.txt");
    echo $footer;
?>
                        </textarea>
                    </div>

                    <div class="form-row">
                        <label for="group">group</label>
                        <p class="helper"><code>group.txt</code> に記述したグループ設定です。</p>
                        <textarea id="group" name="group" rows="10">
<?php
    $group = file_get_contents("group.txt");
    echo $group;
?>
                        </textarea>
                    </div>
                </div>

                <div class="experimental">
                    <h3>試験中オプション</h3>
                    <p class="helper">ここから下のフィールドは現在試験運用中です。</p>
                    <div class="textareas">
                        <div class="form-row">
                            <label for="pickup">pickup</label>
                            <textarea id="pickup" name="pickup" rows="6"></textarea>
                        </div>
                        <div class="form-row">
                            <label for="api">api</label>
                            <textarea id="api" name="api" rows="6"></textarea>
                        </div>
                    </div>
                </div>

                <div class="submit-wrap">
                    <input type="submit" value="出力する">
                </div>
            </form>
        </section>

        <section id="sample" class="card">
            <h2>出力例</h2>
            <figure class="sample">
                <img class="lightbox-target" src="出力例.jpg" alt="出力例">
                <figcaption>生成されたシーケンス図イメージ</figcaption>
            </figure>
        </section>

        <section class="card">
            <h2>関連リンク</h2>
            <p><a href="../">ログ自動埋め込み autoLogInsert ページへ</a></p>
        </section>
    </main>

    <footer class="credit">
      <div class="container">
        <p>by M's Factory 2013-2025</p>
        <p>ご質問は <a href="mailto:mics@oasis.ocn.ne.jp?subject=M's Factory(mkSeq)">こちら</a> へメールして下さい。</p>
        <p><a href="/#">トップへ戻る</a></p>
      </div>
    </footer>

    <div class="image-lightbox" id="imageLightbox" aria-hidden="true">
        <div class="image-lightbox__dialog" role="dialog" aria-modal="true" aria-label="出力例の拡大表示">
            <button type="button" class="image-lightbox__close" aria-label="閉じる">&times;</button>
            <img src="" alt="">
            <p class="image-lightbox__caption"></p>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var lightbox = document.getElementById("imageLightbox");
            var lightboxImages = document.querySelectorAll(".lightbox-target");
            if (!lightbox || !lightboxImages.length) {
                return;
            }

            var lightboxImg = lightbox.querySelector("img");
            var lightboxCaption = lightbox.querySelector(".image-lightbox__caption");
            var closeButton = lightbox.querySelector(".image-lightbox__close");

            var getCaption = function (image) {
                var fig = image.closest("figure");
                var captionEl = fig ? fig.querySelector("figcaption") : null;
                if (captionEl && captionEl.textContent) {
                    return captionEl.textContent.trim();
                }
                return image.getAttribute("alt") || "";
            };

            var openLightbox = function (image) {
                if (!lightboxImg) {
                    return;
                }
                var source = image.currentSrc || image.src;
                if (!source) {
                    return;
                }
                lightboxImg.src = source;
                lightboxImg.alt = image.getAttribute("alt") || "";
                if (lightboxCaption) {
                    lightboxCaption.textContent = getCaption(image);
                }
                lightbox.classList.add("is-visible");
                lightbox.setAttribute("aria-hidden", "false");
                document.body.style.overflow = "hidden";
            };

            var closeLightbox = function () {
                lightbox.classList.remove("is-visible");
                lightbox.setAttribute("aria-hidden", "true");
                if (lightboxImg) {
                    lightboxImg.removeAttribute("src");
                    lightboxImg.alt = "";
                }
                if (lightboxCaption) {
                    lightboxCaption.textContent = "";
                }
                document.body.style.overflow = "";
            };

            lightboxImages.forEach(function (image) {
                image.setAttribute("role", "button");
                image.setAttribute("tabindex", "0");
                var label = image.getAttribute("alt") || "出力例";
                image.setAttribute("aria-label", label + " を拡大表示");
                image.addEventListener("click", function () {
                    openLightbox(image);
                });
                image.addEventListener("keydown", function (event) {
                    if (event.key === "Enter" || event.key === " " || event.key === "Spacebar") {
                        event.preventDefault();
                        openLightbox(image);
                    }
                });
            });

            if (closeButton) {
                closeButton.addEventListener("click", closeLightbox);
            }
            lightbox.addEventListener("click", function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });
            document.addEventListener("keydown", function (event) {
                if ((event.key === "Escape" || event.key === "Esc") && lightbox.classList.contains("is-visible")) {
                    closeLightbox();
                }
            });
        });
    </script>
</body>
</html>
