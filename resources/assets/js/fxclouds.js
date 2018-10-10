import * as THREE from 'three';
import Detector from './lib/three.detector';

if (!Detector.webgl) {
	Detector.addGetWebGLMessage();
}

let container;
let camera, scene, renderer;
let mesh, geometry, material;

let mouseX = 0, mouseY = 0;
const start_time = Date.now();

const windowHalfX = window.innerWidth / 2;
const windowHalfY = window.innerHeight / 2;

initClouds();

function initClouds() {

	container = document.getElementById('cd-main-overlay');

	// Bg gradient

	const canvas = document.createElement('canvas');
	canvas.width = 32;
	canvas.height = window.innerHeight;

	const context = canvas.getContext('2d');

	const gradient = context.createLinearGradient(0, 0, 0, canvas.height);
	gradient.addColorStop(0, '#1e4877');
	gradient.addColorStop(0.5, '#4584b4');

	context.fillStyle = gradient;
	context.fillRect(0, 0, canvas.width, canvas.height);

	container.style.background = `url("${canvas.toDataURL('image/png')}")`;

	//

	camera = new THREE.PerspectiveCamera(30, window.innerWidth / window.innerHeight, 1, 3000);
	camera.position.z = 3000;

	scene = new THREE.Scene();

	geometry = new THREE.Geometry();

	const texture = THREE.ImageUtils.loadTexture('/images/cloud10.png', null, animateClouds);
	texture.magFilter = THREE.LinearMipMapLinearFilter;
	texture.minFilter = THREE.LinearMipMapLinearFilter;

	const fog = new THREE.Fog(0x4584b4, -100, 3000);

	material = new THREE.ShaderMaterial({
		uniforms: {
			map: {
				type: 't',
				value: texture
			},
			fogColor: {
				type: 'c',
				value: fog.color
			},
			fogNear: {
				type: 'f',
				value: fog.near
			},
			fogFar: {
				type: 'f',
				value: fog.far
			}
		},
		vertexShader: document.getElementById('vs').textContent,
		fragmentShader: document.getElementById('fs').textContent,
		depthWrite: false,
		depthTest: false,
		transparent: true

	});

	const plane = new THREE.Mesh(new THREE.PlaneGeometry(64, 64));

	for (let i = 0; i < 8000; i++) {

		plane.position.x = Math.random() * 1000 - 500;
		plane.position.y = -Math.random() * Math.random() * 200 - 15;
		plane.position.z = i;
		plane.rotation.z = Math.random() * Math.PI;
		plane.scale.x = plane.scale.y = Math.random() * Math.random() * 1.5 + 0.5;

		THREE.GeometryUtils.merge(geometry, plane);

	}

	mesh = new THREE.Mesh(geometry, material);
	scene.add(mesh);

	mesh = new THREE.Mesh(geometry, material);
	mesh.position.z = -8000;
	scene.add(mesh);

	renderer = new THREE.WebGLRenderer({
		antialias: false
	});
	renderer.setSize(window.innerWidth, window.innerHeight);
	container.append(renderer.domElement);

	document.addEventListener('mousemove', onDocumentMouseMoveClouds, false);
	window.addEventListener('resize', onWindowResizeClouds, false);

}

function onDocumentMouseMoveClouds(event) {

	mouseX = (event.clientX - windowHalfX) * 0.25;
	mouseY = (event.clientY - windowHalfY) * 0.15;

}

function onWindowResizeClouds() {

	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();

	renderer.setSize(window.innerWidth, window.innerHeight);

}

function animateClouds() {

	requestAnimationFrame(animateClouds);

	if (container.style.visibility !== 'hidden') {

		let position = ((Date.now() - start_time) * 0.03) % 8000;

		camera.position.x += (mouseX - camera.position.x) * 0.005;
		camera.position.y += (-mouseY - 70 - camera.position.y) * 0.01;
		camera.position.z = -position + 8000;

		renderer.render(scene, camera);
	}
}
