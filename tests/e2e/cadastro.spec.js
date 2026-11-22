import { expect, test } from "@playwright/test";

test("fluxo de cadastro contra API PHP real", async ({ page }) => {
    await page.goto("/index.html");
    await page.fill("#nome", "Maria Souza");
    await page.fill("#email", "maria-e2e@example.com");
    await page.fill("#telefone", "21988887777");
    await page.fill("#cpf_pessoal", "52998224725");
    await page.fill("#data_nascimento", "1995-06-15");
    await page.fill("#cep", "22760000");
    await page.fill("#logradouro", "Av. das Américas");
    await page.fill("#numero", "100");
    await page.click("#btn-passo2");

    await page.fill("#cartao", "4111111111111111");
    await page.fill("#validade", "1230");
    await page.fill("#cvv", "123");
    await page.fill("#nome_cartao", "Maria Souza");
    await page.fill("#cpf_cartao", "52998224725");
    await page.click("#btn-finalizar");

    await expect(page.locator("#step-sucesso")).toBeVisible({ timeout: 15000 });
    await expect(page.getByText("Sucesso.")).toBeVisible();
});