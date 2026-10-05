/**
 * Revela as seções conforme entram na viewport.
 * Estilo funcional: funções puras + composição, sem estado global mutável.
 * Degrada com elegância: sem JS ou sem IntersectionObserver, o CSS já mostra
 * tudo (a classe é adicionada de imediato); com `prefers-reduced-motion`,
 * a transição é desligada no CSS.
 */

const CLASSE_VISIVEL = 'is-visivel';
const SELETOR = '[data-revelar]';

const prefereMenosMovimento = () =>
  window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const suportaObserver = () => 'IntersectionObserver' in window;

const revelar = (elemento) => elemento.classList.add(CLASSE_VISIVEL);

const aoCruzar = (observador) => (entradas) =>
  entradas
    .filter((entrada) => entrada.isIntersecting)
    .forEach((entrada) => {
      revelar(entrada.target);
      observador.unobserve(entrada.target);
    });

const criarObservador = () => {
  const observador = new IntersectionObserver(
    (entradas) => aoCruzar(observador)(entradas),
    { rootMargin: '0px 0px -12% 0px', threshold: 0.08 }
  );
  return observador;
};

const iniciar = () => {
  const alvos = Array.from(document.querySelectorAll(SELETOR));

  if (prefereMenosMovimento() || !suportaObserver()) {
    alvos.forEach(revelar);
    return;
  }

  const observador = criarObservador();
  alvos.forEach((alvo) => observador.observe(alvo));
};

iniciar();
