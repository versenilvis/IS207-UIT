<script type="module">
  import { Renderer, Program, Mesh, Color, Triangle } from 'https://cdn.skypack.dev/ogl';

  const vertexShader = `
    attribute vec2 uv;
    attribute vec2 position;
    varying vec2 vUv;
    void main() {
      vUv = uv;
      gl_Position = vec4(position, 0, 1);
    }
  `;

  const fragmentShader = `
    precision highp float;
    uniform float uTime;
    uniform vec3 uColor;
    uniform vec3 uResolution;
    uniform vec2 uMouse;
    uniform float uAmplitude;
    uniform float uSpeed;
    varying vec2 vUv;
    void main() {
      float mr = min(uResolution.x, uResolution.y);
      vec2 uv = (vUv.xy * 2.0 - 1.0) * uResolution.xy / mr;
      uv += (uMouse - vec2(0.5)) * uAmplitude;
      float d = -uTime * 0.5 * uSpeed;
      float a = 0.0;
      for (float i = 0.0; i < 8.0; ++i) {
        a += cos(i - d - a * uv.x);
        d += sin(uv.y * i + a);
      }
      d += uTime * 0.5 * uSpeed;
      vec3 col = vec3(cos(uv * vec2(d, a)) * 0.6 + 0.4, cos(a + d) * 0.5 + 0.5);
      col = cos(col * cos(vec3(d, a, 2.5)) * 0.5 + 0.5) * uColor;
      gl_FragColor = vec4(col, 1.0);
    }
  `;

  const init = () => {
    const ctn = document.querySelector('.iridescence-container');
    if (!ctn) return;

    if (ctn.querySelector('canvas')) return;

    const renderer = new Renderer({ alpha: true, premultipliedAlpha: false });
    const gl = renderer.gl;
    let program;

    function resize() {
      const width = ctn.clientWidth || 340;
      const height = ctn.clientHeight || 140;
      renderer.setSize(width, height);
      if (program) {
        program.uniforms.uResolution.value = new Color(
          gl.canvas.width,
          gl.canvas.height,
          gl.canvas.width / gl.canvas.height
        );
      }
    }

    window.addEventListener('resize', resize);
    
    const geometry = new Triangle(gl);
    program = new Program(gl, {
      vertex: vertexShader,
      fragment: fragmentShader,
      uniforms: {
        uTime:       { value: 0 },
        uColor:      { value: new Color(1, 1, 1) },
        uResolution: { value: new Color(100, 100, 1) },
        uMouse:      { value: new Float32Array([0.5, 0.5]) },
        uAmplitude:  { value: 0.05 },
        uSpeed:      { value: 0.4 }
      }
    });

    const mesh = new Mesh(gl, { geometry, program });
    
    function update(t) {
      requestAnimationFrame(update);
      program.uniforms.uTime.value = t * 0.001;
      renderer.render({ scene: mesh });
    }

    ctn.appendChild(gl.canvas);
    resize();
    requestAnimationFrame(update);
  };

  window.dismissProCard = function(event) {
    if (event) event.stopPropagation();
    const proCard = document.querySelector('.pro-card');
    if (proCard) {
      proCard.classList.remove('pro-card--visible');
    }
  };

  const showProCard = () => {
    init();
    setTimeout(() => {
      const proCard = document.querySelector('.pro-card');
      if (proCard) {
        proCard.classList.add('pro-card--visible');
      }
    }, 1200);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showProCard);
  } else {
    showProCard();
  }
</script>
