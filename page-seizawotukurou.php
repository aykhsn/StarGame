<?php
/*
Template Name: せいざをつくろう
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> <?php Arkhe::root_attrs(); ?>>
<head>
<meta charset="utf-8">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, viewport-fit=cover">
<?php
	wp_head();
	$setting = Arkhe::get_setting(); // SETTING取得
?>
<style>
    body {
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #7c74dd;
        overflow: hidden;
    }  
</style>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3/dist/phaser.min.js"></script>
    <script>
    const assetsUrl = "<?php echo get_template_directory_uri(); ?>/assets/games";　　　　　　　

    const config = {
        type: Phaser.AUTO,
        width: window.innerWidth,
        height: window.innerHeight,
        backgroundColor: '#7c74dd',
        scale: {
            mode: Phaser.Scale.FIT,
            autoCenter: Phaser.Scale.CENTER_BOTH
        },
        scene: {
            preload: preload,
            create: create,
            update: update
        }
    };

    const game = new Phaser.Game(config);

    function preload() {
        this.load.svg('star',  `${assetsUrl}/star.svg`, { width: 200, height: 200 });
        this.load.audio('bounce',  `${assetsUrl}/kiran.mp3`);
        this.load.audio('kirakirarin',  `${assetsUrl}/kirakirarin.mp3`);
        this.load.audio('yatta',  `${assetsUrl}/yattane.mp3`);
        this.load.audio('appear',  `${assetsUrl}/pecho.mp3`);
    }

    function create() {
        const starSize = 140;
        const positions = [];
        let lastStar = null;

        // 効果音の設定
        const bounceSound = this.sound.add('bounce');
        const kirakirarinSound = this.sound.add('kirakirarin');
        const yattaSound = this.sound.add('yatta');
        const appearSound = this.sound.add('appear');

        // 背景のキラキラ星を配置
        const numBackgroundStars = 90;
        const pastelColors = [0xffb3ba, 0xffdfba, 0xffffba, 0xbaffc9, 0xbae1ff];

        function drawStar(graphics, x, y, size, color) {
            graphics.fillStyle(color, 1.0);
            graphics.beginPath();
            for (let j = 0; j < 5; j++) {
                const angle = Phaser.Math.DegToRad(72 * j - 90);
                const outerX = x + Math.cos(angle) * size;
                const outerY = y + Math.sin(angle) * size;
                graphics.lineTo(outerX, outerY);
                const innerAngle = angle + Phaser.Math.DegToRad(36);
                const innerX = x + Math.cos(innerAngle) * (size / 2.5);
                const innerY = y + Math.sin(innerAngle) * (size / 2.5);
                graphics.lineTo(innerX, innerY);
            }
            graphics.closePath();
            graphics.fillPath();
        }

        for (let i = 0; i < numBackgroundStars; i++) {
            let x = Phaser.Math.Between(0, this.scale.width);
            let y = Phaser.Math.Between(0, this.scale.height);
            let size = Phaser.Math.Between(4, 10); // サイズをランダムに設定
            let color = Phaser.Utils.Array.GetRandom(pastelColors); // パステルカラーをランダムに選択
            let alphaFrom = Phaser.Math.FloatBetween(0.3, 0.7);
            let alphaTo = Phaser.Math.FloatBetween(0.8, 1);
            let duration = Phaser.Math.Between(800, 1500); // 瞬きのタイミングをランダムに設定

            let graphics = this.add.graphics();
            drawStar(graphics, x, y, size, color);
            graphics.alpha = alphaFrom;

            this.add.tween({
                targets: graphics,
                alpha: { from: alphaFrom, to: alphaTo },
                duration: duration,
                yoyo: true,
                repeat: -1
            });
        }

        this.graphics = this.add.graphics({ lineStyle: { width: 4, color: 0xFFF97D } });

        const stars = this.add.group();

        const createStars = (scene) => {
            positions.length = 0; // Clear positions array
            stars.clear(true, true); // Clear stars group
            lastStar = null; // Reset lastStar

            for (let i = 0; i < Phaser.Math.Between(4, 6); i++) {
                let x, y, overlapping;

                do {
                    x = Phaser.Math.Between(starSize / 2, scene.scale.width - starSize / 2);
                    y = Phaser.Math.Between(starSize / 2, scene.scale.height - starSize / 2);
                    overlapping = positions.some(pos => {
                        return Phaser.Math.Distance.Between(x, y, pos.x, pos.y) < starSize;
                    });
                } while (overlapping);

                positions.push({ x, y });

                let star = scene.add.image(x, y, 'star').setInteractive();
                star.setDisplaySize(starSize, starSize);

                // 星がゆらゆら揺れるアニメーション
                scene.tweens.add({
                    targets: star,
                    angle: { from: -5, to: 5 },
                    duration: Phaser.Math.Between(1400, 2000),
                    yoyo: true,
                    repeat: -1,
                    ease: 'Sine.easeInOut',
                    delay: Phaser.Math.Between(-200, 0) // 揺れるタイミングをランダムに設定
                });

                star.on('pointerdown', function () {
                    bounceSound.play(); // 効果音を再生

                    scene.tweens.add({
                        targets: this,
                        scaleX: 0.2,
                        scaleY: 0.2,
                        duration: 500,
                        ease: 'Bounce.easeOut',
                        onComplete: () => {
                            this.disableInteractive(); // 一度クリックされたらインタラクティブを無効にする

                            // 全ての星が小さくなったかチェック
                            if (stars.getChildren().every(s => s.scaleX === 0.2 && s.scaleY === 0.2)) {
                                setTimeout(() => {
                                    scene.tweens.add({
                                        targets: scene.graphics,
                                        alpha: 0,
                                        duration: 4000,
                                        ease: 'Power2',
                                        onComplete: () => {
                                            scene.graphics.clear();
                                            scene.graphics = scene.add.graphics({ lineStyle: { width: 4, color: 0xFFF97D } }); // Reset graphics
                                        }
                                    });

                                    stars.getChildren().forEach(star => {
                                        kirakirarinSound.play();
                                        setTimeout(() => {
                                            yattaSound.play(); // 全ての星が消えた2秒後に「やったー」を再生
                                        }, 1500);

                                        scene.tweens.add({
                                            targets: star,
                                            x: `-=${Phaser.Math.Between(-800, 800)}`,
                                            y: `-=${Phaser.Math.Between(-800, 800)}`,
                                            alpha: 0,
                                            duration: 3000,
                                            ease: 'Power2',
                                            onComplete: () => {
                                                star.destroy();

                                                // 全ての星が消えたかチェック
                                                if (stars.getChildren().length === 0) {
                                                    setTimeout(() => {
                                                        createStars(scene); // 新しい星を生成してゲームを繰り返す
                                                        appearSound.play();
                                                    }, 1000);
                                                }
                                            }
                                        });
                                    });
                                }, 600);
                            }
                        }
                    });

                    if (lastStar) {
                        scene.graphics.lineBetween(lastStar.x, lastStar.y, this.x, this.y);
                    }

                    lastStar = this;
                });

                stars.add(star);
            }
        }

        createStars(this);
    }

    function update() {
        // No need for update logic in this case
    }

    window.addEventListener('resize', () => {
        game.scale.resize(window.innerWidth, window.innerHeight);
    });

    </script>
</body>
</html>