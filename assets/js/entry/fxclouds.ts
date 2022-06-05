import {
	Fog,
	Geometry,
	Mesh,
	PerspectiveCamera,
	PlaneGeometry,
	Scene,
	ShaderMaterial,
	TextureLoader,
	WebGLRenderer,
} from 'three';
import Detector from '../lib/external/three.detector';

(() => {
	if (!Detector.webgl) {
		Detector.addGetWebGLMessage();
		return;
	}

	let mouseX = 0;
	let mouseY = 0;
	const startTime = Date.now();

	const windowHalfX = window.innerWidth / 2;
	const windowHalfY = window.innerHeight / 2;

	const vertexShader = `
varying vec2 vUv;

void main() {
	vUv = uv;
	gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
}
`;

	const fragmentShader = `
uniform sampler2D map;

uniform vec3 fogColor;
uniform float fogNear;
uniform float fogFar;

varying vec2 vUv;

void main() {
	float depth = gl_FragCoord.z / gl_FragCoord.w;
	float fogFactor = smoothstep( fogNear, fogFar, depth );

	gl_FragColor = texture2D( map, vUv );
	gl_FragColor.w *= pow( abs(gl_FragCoord.z), 20.0 );
	gl_FragColor = mix( gl_FragColor, vec4( fogColor, gl_FragColor.w ), fogFactor );
}
`;

	const container = document.createElement('div');
	Object.assign(container.style, {
		position: 'fixed',
		left: '0',
		right: '0',
		bottom: '0',
		top: '0',
		zIndex: '-1',
		background: 'linear-gradient(#1e4877, #4584b4, #4584b4)',
	});
	document.body.appendChild(container);

	const canvas = document.createElement('canvas');
	canvas.width = 32;
	canvas.height = window.innerHeight;

	const camera = new PerspectiveCamera(
		30,
		window.innerWidth / window.innerHeight,
		1,
		3000
	);
	camera.position.z = 3000;

	const scene = new Scene();
	const geometry = new Geometry();

	const texture = new TextureLoader().load(
		'/images/cloud10.png',
		animateClouds
	);

	const fog = new Fog(0x4584b4, -100, 3000);

	const material = new ShaderMaterial({
		depthTest: false,
		depthWrite: false,
		transparent: true,
		uniforms: {
			fogColor: { value: fog.color },
			fogFar: { value: fog.far },
			fogNear: { value: fog.near },
			map: { value: texture },
		},
		fragmentShader,
		vertexShader,
	});

	const plane = new Mesh(new PlaneGeometry(64, 64));

	for (let i = 0; i < 8000; i++) {
		plane.position.x = Math.random() * 1000 - 500;
		plane.position.y = -Math.random() * Math.random() * 200 - 15;
		plane.position.z = i;
		plane.rotation.z = Math.random() * Math.PI;
		plane.scale.x = plane.scale.y = Math.random() * Math.random() * 1.5 + 0.5;

		plane.updateMatrix();
		geometry.merge(plane.geometry as Geometry, plane.matrix);
	}

	const mesh1 = new Mesh(geometry, material);
	scene.add(mesh1);

	const mesh2 = new Mesh(geometry, material);
	mesh2.position.z = -8000;
	scene.add(mesh2);

	const renderer = new WebGLRenderer({
		alpha: true,
		antialias: false,
	});
	renderer.setSize(window.innerWidth, window.innerHeight);
	container.append(renderer.domElement);

	document.addEventListener('mousemove', (event) => {
		mouseX = (event.clientX - windowHalfX) * 0.25;
		mouseY = (event.clientY - windowHalfY) * 0.15;
	});

	window.addEventListener('resize', () => {
		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();
		renderer.setSize(window.innerWidth, window.innerHeight);
	});

	function animateClouds() {
		requestAnimationFrame(animateClouds);

		const position = ((Date.now() - startTime) * 0.03) % 8000;
		camera.position.x += (mouseX - camera.position.x) * 0.005;
		camera.position.y += (-mouseY - 70 - camera.position.y) * 0.01;
		camera.position.z = -position + 8000;
		renderer.render(scene, camera);
	}
})();
