/** Estados visuais alinhados às classes Tailwind do design system. */

export function setInputState(input, valid) {
    input.classList.toggle("input-invalid", !valid);
    input.classList.toggle("input-valid", valid);
}

export function setErrorVisible(el, visible) {
    el.classList.toggle("is-visible", visible);
    el.classList.toggle("hidden", !visible);
}