import {
	AdditiveBlending,
	BufferGeometry,
	Color,
	Float32BufferAttribute,
	FogExp2,
	PerspectiveCamera,
	Points,
	PointsMaterial,
	Scene,
	SubtractiveBlending,
	Texture,
	TextureLoader,
	WebGLRenderer,
} from 'three';
import { docReady, isLightMode } from '../lib/util';

let camera: PerspectiveCamera;
let scene: Scene;
let renderer: WebGLRenderer;
const materials: PointsMaterial[] = [];
let parameters: Array<{ color: number[]; sprite: Texture; size: number }>;

docReady(() => {
	const lightTheme = isLightMode();

	try {
		init();
		animate();
	} catch (e) {
		console.log(e);

		// negeer fout
	}

	function init() {
		const container = document.createElement('div');
		Object.assign(container.style, {
			position: 'fixed',
			left: '0',
			right: '0',
			bottom: '0',
			top: '0',
			zIndex: '-1',
			background: 'transparent',
		});
		document.body.appendChild(container);
		camera = new PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 2000);
		camera.position.z = 1000;

		scene = new Scene();
		if (lightTheme) {
			scene.background = new Color(1, 1, 1);
		}
		scene.fog = new FogExp2(0x000000, 0.0008);

		const geometry = new BufferGeometry();
		const vertices = [];

		const textureLoader = new TextureLoader();

		const sprite1 = textureLoader.load('/images/sneeuw/snowflake1.png');
		const sprite2 = textureLoader.load('/images/sneeuw/snowflake2.png');
		const sprite3 = textureLoader.load('/images/sneeuw/snowflake3.png');
		const sprite4 = textureLoader.load('/images/sneeuw/snowflake4.png');
		const sprite5 = textureLoader.load('/images/sneeuw/snowflake5.png');

		for (let i = 0; i < 10000; i++) {
			vertices.push(Math.random() * 2000 - 1000, Math.random() * 2000 - 1000, Math.random() * 2000 - 1000);
		}

		geometry.setAttribute('position', new Float32BufferAttribute(vertices, 3));

		parameters = [
			{ color: [0.55, 0.2, 0.5], sprite: sprite2, size: 20 },
			{ color: [0.45, 0.1, 0.5], sprite: sprite3, size: 15 },
			{ color: [0.4, 0.05, 0.5], sprite: sprite1, size: 10 },
			{ color: [0.35, 0, 0.5], sprite: sprite5, size: 8 },
			{ color: [0.3, 0, 0.5], sprite: sprite4, size: 5 },
		];

		for (let i = 0; i < parameters.length; i++) {
			const { color, sprite, size } = parameters[i];

			materials[i] = new PointsMaterial({
				size,
				map: sprite,
				blending: lightTheme ? SubtractiveBlending : AdditiveBlending,
				depthTest: false,
				transparent: true,
			});

			// Draai de hue om in light theme omdat we nu in SubtractiveBlending zitten.
			const hue = lightTheme ? (color[0] + 0.5) % 1 : color[0];

			materials[i].color.setHSL(hue, color[1], color[2]);

			const particles = new Points(geometry, materials[i]);

			particles.rotation.x = Math.random() * 6;
			particles.rotation.y = Math.random() * 6;
			particles.rotation.z = Math.random() * 6;

			scene.add(particles);
		}

		//

		renderer = new WebGLRenderer();
		renderer.setPixelRatio(window.devicePixelRatio);
		renderer.setSize(window.innerWidth, window.innerHeight);
		container.appendChild(renderer.domElement);

		//

		window.addEventListener('resize', onWindowResize, false);
	}

	function onWindowResize() {
		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();

		renderer.setSize(window.innerWidth, window.innerHeight);
	}

	//

	function animate() {
		requestAnimationFrame(animate);

		render();
	}

	function render() {
		const time = Date.now() * 0.00005;

		camera.position.x += camera.position.x * 0.05;
		camera.position.y += camera.position.y * 0.05;

		camera.lookAt(scene.position);

		for (let i = 0; i < scene.children.length; i++) {
			const object = scene.children[i];

			if (object instanceof Points) {
				object.rotation.y = time * (i < 4 ? i + 1 : -(i + 1));
			}
		}

		// for (let i = 0; i < materials.length; i++) {
		// 	const color = parameters[i].color;
		// 	const h = (360 * (color[0] + time) % 360) / 360;
		//
		// 	materials[i].color.setHSL(h, color[1], color[2]);
		// }
		renderer.render(scene, camera);
	}
});
