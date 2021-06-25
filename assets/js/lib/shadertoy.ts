import {
	Mesh,
	OrthographicCamera,
	PlaneBufferGeometry,
	Scene,
	ShaderMaterial,
	Vector3,
	WebGLRenderer,
} from 'three';

/**
 * Gedeeltelijke implementatie van shadertoy.
 *
 * Zie https://shadertoy.com, in principe kan je een shader kopieren van die site. Zolang het een enkele shader is,
 * dus zonder buffers.
 *
 * Supported:
 * - iResolution
 * - iTime
 *
 * Niet supported:
 * - iTimeDelta
 * - iFrame
 * - iChannelTime[4]
 * - iChannelResolution[4]
 * - iMouse
 * - iChannel[4]
 * - iDate
 * - iSampleRate
 *
 * @param shader
 */
export function shaderToy(shader: string): void {
	const container = document.createElement('div');
	Object.assign(container.style, {
		position: 'fixed',
		left: '0',
		right: '0',
		bottom: '0',
		top: '0',
		zIndex: '-1',
	});
	document.body.appendChild(container);

	const renderer = new WebGLRenderer();
	renderer.domElement.style.width = '100%';
	renderer.domElement.style.height = '100%';
	container.append(renderer.domElement);
	renderer.autoClearColor = false;

	const camera = new OrthographicCamera(-1, 1, 1, -1, -1, 1);
	const scene = new Scene();
	const plane = new PlaneBufferGeometry(2, 2);

	const fragmentShader = `
uniform vec3      iResolution;           // viewport resolution (in pixels)
uniform float     iTime;                 // shader playback time (in seconds)
// uniform float     iTimeDelta;            // render time (in seconds)
// uniform int       iFrame;                // shader playback frame
// uniform float     iChannelTime[4];       // channel playback time (in seconds)
// uniform vec3      iChannelResolution[4]; // channel resolution (in pixels)
// uniform vec4      iMouse;                // mouse pixel coords. xy: current (if MLB down), zw: click
// uniform sampler2D iChannel0;			// input channel. XX = 2D/Cube
// uniform sampler2D iChannel1;
// uniform sampler2D iChannel2;
// uniform sampler2D iChannel3;
// uniform vec4      iDate;                 // (year, month, day, time in seconds)
// uniform float     iSampleRate;           // sound sample rate (i.e., 44100)

${shader}
void main() {
	mainImage(gl_FragColor, gl_FragCoord.xy);
}
  `;
	// const loader = new TextureLoader();
	// const texture = loader.load('https://threejsfundamentals.org/threejs/resources/images/bayer.png');
	// texture.minFilter = NearestFilter;
	// texture.magFilter = NearestFilter;
	// texture.wrapS = RepeatWrapping;
	// texture.wrapT = RepeatWrapping;
	const uniforms = {
		iTime: {value: 0},
		iResolution: {value: new Vector3()},
		// iChannel0: {value: texture},
	};
	const material = new ShaderMaterial({
		fragmentShader,
		uniforms,
	});
	scene.add(new Mesh(plane, material));

	function resizeRendererToDisplaySize() {
		const canvas = renderer.domElement;
		const width = canvas.clientWidth;
		const height = canvas.clientHeight;
		const needResize = canvas.width !== width || canvas.height !== height;
		if (needResize) {
			renderer.setSize(width, height, false);
		}
		return needResize;
	}

	function render(time: number) {
		time *= 0.001;  // convert to seconds

		resizeRendererToDisplaySize();

		const canvas = renderer.domElement;
		uniforms.iResolution.value.set(canvas.width, canvas.height, 1);
		uniforms.iTime.value = time;

		renderer.render(scene, camera);

		requestAnimationFrame(render);
	}

	requestAnimationFrame(render);
}
