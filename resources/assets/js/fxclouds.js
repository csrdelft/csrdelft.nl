import * as THREE from 'three';

import {Detector} from './lib/three.detector';

window.THREE = THREE;
window["Detector"] = Detector;
