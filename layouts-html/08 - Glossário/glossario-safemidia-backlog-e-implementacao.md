# Glossário do Seguro Safe Mídia
## Backlog de termos + Guia de implementação no WordPress

Documento de trabalho consolidado. Reúne a base de termos (Fenacor + CNseg + sugestões próprias), as regras editoriais, a priorização em três ondas de produção e o passo a passo técnico para colocar tudo no ar com WordPress + Elementor.

Legenda de fontes: **F** = Glossário Fenacor · **C** = Glossário CNseg (edição atualizada em outubro/2025) · **S** = Sugestão Safe Mídia (termos de mercado e noticiário ausentes das duas fontes).

Legenda de leitor-alvo: **SEG** = Segurado/consumidor (linguagem sem jargão, jargão zero na definição direta) · **PRO** = Profissional do mercado (linguagem técnica, sem didatismo excessivo).

---

## 1. Regras editoriais (inegociáveis)

1. **Definição direta em 40 a 60 palavras, autossuficiente.** Nos verbetes SEG, a definição não pode conter nenhuma palavra que seja verbete do próprio glossário. Se contém, reescreve. Jargão só entra no corpo do texto, sempre apresentado ("o nome técnico disso é X"), nunca pressuposto.
2. **Teste de aprovação SEG:** ler a definição em voz alta para alguém de fora do mercado e perguntar o que entendeu. Se precisou explicar, reprova.
3. **Cada verbete declara seu leitor-alvo** (campo no CMS). O nível de vocabulário segue essa declaração.
4. **Nunca copiar as definições da Fenacor, CNseg ou Susep.** As fontes servem para escopo e checagem técnica. O texto é 100% autoral, esse é o diferencial de ranqueamento e citabilidade.
5. **Estrutura fixa do verbete:** definição direta > linha de revisão (data + revisor técnico) > como funciona na prática > exemplo com números > confusão comum (quando existir) > FAQ (3 perguntas) > termos relacionados > bloco automático "no noticiário" (por tag).
6. **Todo dado numérico do exemplo precisa ser plausível e verificado.** Valores de franquia, percentuais de reajuste e limites legais mudam; revisar anualmente (a data de revisão fica visível no verbete).
7. **Termos sensíveis** (ex.: Morte Voluntária, que trata de suicídio no seguro de vida): redigir com cuidado redobrado, linguagem neutra e factual, sem detalhamento desnecessário, e sempre revisão humana antes de publicar.

---

## 2. Backlog de termos por onda de produção

Após fusões e cortes (termos fracos da Fenacor eliminados, famílias de termos agrupadas num verbete só), a base final tem **cerca de 185 verbetes**. Ritmo sugerido: ondas 1 e 2 antes ou junto do lançamento; onda 3 a 5 verbetes por semana.

### Onda 1 · Termos de busca do consumidor (46 verbetes)

Prioridade máxima. São os termos com volume real de busca do segurado e a base da malha de links da trilha "Para o Segurado".

| Termo | Fonte | Leitor | Observações |
|---|---|---|---|
| Apólice | F | SEG | |
| Assistência 24 horas | F/C | SEG | |
| Aviso de Sinistro | F | SEG | Unificar com "Comunicação do Sinistro" |
| Beneficiário | F | SEG | |
| Boletim de Ocorrência (B.O.) | F | SEG | |
| Bônus | F/C | SEG | Classe de bônus na renovação |
| Cancelamento | F | SEG | |
| Capital Segurado | F/C | SEG | |
| Carência | F/C | SEG | |
| Carro Reserva | C | SEG | |
| Carta Verde | C | SEG | Viagem ao Mercosul |
| Cobertura | F | SEG | |
| Cobertura Compreensiva | S/C | SEG | |
| Condutor (principal) | F | SEG | |
| Coparticipação | S/C | SEG | Planos de saúde |
| Corretor de Seguros | F | SEG | |
| Doença ou Lesão Preexistente (DLP) | S/C | SEG | |
| Endosso | F | SEG | Citar "Aditivo" e "Endosso de Substituição" como variações |
| Franquia | F/C | SEG | Verbete modelo já pronto |
| Furto x Roubo | F | SEG | Um verbete só; incluir Furto Qualificado como seção |
| Garantia Estendida | C | SEG | Incluir Garantia Legal e Contratual como seções |
| Importância Segurada | F | SEG | |
| Indenização | F | SEG | |
| Indenização Integral | S/C | SEG | Incluir Perda Total como seção |
| Perfil (questionário de risco) | S | SEG | |
| Plano de Saúde x Seguro Saúde | S | SEG | |
| Portabilidade | S/C | SEG | Saúde e previdência no mesmo verbete |
| Prêmio | F/C | SEG | A confusão nº 1 do setor (prêmio x mensalidade) |
| Proposta | F | SEG | |
| Reajuste (planos de saúde) | S/C | SEG | Incluir reajuste por faixa etária |
| Rede Credenciada x Livre Escolha | C | SEG | |
| Reembolso | F/C | SEG | |
| Renovação | S | SEG | Incluir renovação automática |
| Salvado | F | SEG | |
| Seguro Condomínio | C | SEG | Obrigatório x residencial |
| Seguro de Animais (Pet) | C | SEG | |
| Seguro de Vida | C | SEG | Incluir seguro de vida resgatável como seção |
| Seguro Prestamista | S/C | SEG | |
| Seguro Residencial | S/C | SEG | |
| Seguro Viagem | S/C | SEG | |
| Sinistro | F | SEG | |
| SPVAT (antigo DPVAT) | S/C | SEG | |
| Terceiro | F | SEG | |
| Valor de Mercado Referenciado (VMR) x Valor Determinado | S/C | SEG | Consolida a família "Valor..." da Fenacor (Valor Atual, de Novo, de Mercado, Indenizável, Médio de Mercado) |
| Vigência | F | SEG | Absorve Duração do Seguro, Prazo Curto e Plurianuais |
| Vistoria Prévia | F/S | SEG | Incluir Avaria Pré-Existente |

### Onda 2 · Termos do noticiário e do profissional (45 verbetes)

São os termos que a redação vai linkar toda semana nas matérias. Constroem a malha de links do conteúdo B2B.

| Termo | Fonte | Leitor | Observações |
|---|---|---|---|
| ANS | S | SEG/PRO | Agência: o que faz, o que regula |
| Averbação | S/C | PRO | Transportes |
| CNSP | S | PRO | |
| Cosseguro | F | PRO | |
| Cyber Seguro (Riscos Cibernéticos) | S/C | PRO | Tipos de ataque como seções |
| D&O | S/C | PRO | |
| E&O (RC Profissional) | S/C | PRO | |
| Embedded Insurance (Seguro Embutido) | S | PRO | |
| Estipulante | F/C | PRO | |
| Fiança Locatícia | S/C | SEG/PRO | |
| Fraude contra o Seguro | S | PRO | |
| IBNR | S | PRO | |
| Insurtech | S | PRO | |
| Limite Máximo de Garantia (LMG) | S/C | PRO | |
| Limite Máximo de Indenização (LMI) | F | PRO | Par com LMG |
| Lucros Cessantes | C | PRO | |
| Marco Legal dos Seguros (Lei 15.040) | S | PRO | Verbete vivo, atualizar conforme regulamentação |
| Microsseguro | S/C | SEG/PRO | |
| MIP e DFI | C | SEG/PRO | Coberturas do habitacional |
| Mutualismo | F | SEG | Absorve "Mútuo" |
| Open Insurance | S | PRO | |
| Prescrição | F | PRO | |
| Provisões Técnicas | S/F | PRO | Substitui o termo antigo "Reserva Técnica" |
| PSR (Subvenção ao Seguro Rural) | C | PRO | |
| Regulação de Sinistro | S | PRO | Incluir Regulador e Liquidação de Sinistros |
| Responsabilidade Civil Geral | C | PRO | |
| Resseguro | F | PRO | |
| Resseguradora (local, admitida, eventual) | F/S | PRO | Absorve "Ressegurador" |
| Retrocessão | F | PRO | |
| Risco | F | PRO | Incluir Agravação e Classe do Risco como seções |
| Risco de Base | C | PRO | Paramétrico |
| Run-off | S | PRO | |
| Seguro Agrícola | S/C | PRO | |
| Seguro Garantia | S/C | PRO | Modalidades (Licitante, Judicial, Execução Fiscal, Aduaneiro, Retenção) como seções |
| Seguro Habitacional | S/C | SEG/PRO | |
| Seguro Paramétrico | S/C | PRO | |
| Seguro Pecuário | S/C | PRO | Incluir Pecuário Faturamento |
| Sinistralidade | S | PRO | |
| Solvência | S | PRO | |
| Stop Loss | C | PRO | |
| Sub-Rogação | F | PRO | |
| Subscrição (Underwriting) | S | PRO | Incluir Subscritor e Aceitação |
| Susep | S | SEG/PRO | |
| Tomador | S/C | PRO | Seguro garantia e riscos financeiros |
| ZARC | C | PRO | |

### Onda 3 · Cauda técnica e taxonomia de produtos (~94 verbetes)

Produção contínua, 5 por semana. Termos da base clássica Fenacor + taxonomia de produtos da CNseg + previdência, capitalização e saúde.

| Termo | Fonte | Leitor | Observações |
|---|---|---|---|
| Acessórios | F | SEG | Contexto auto |
| Acidente / Evento | F | SEG | Um verbete |
| Acidente Pessoal | F | SEG | |
| Acidentes Pessoais de Passageiros (APP) | F/C | SEG | |
| Adesão (contrato de adesão) | F | PRO | |
| Administradora de Benefícios | C | PRO | |
| Apólice Coletiva x Individual | F | PRO | Um verbete |
| Assistência Funeral | S/C | SEG | |
| Ativos Garantidores | S | PRO | |
| Atuário | S | PRO | |
| Autogestão (modalidade de operadora) | C | PRO | |
| Avaria Grossa | C | PRO | Marítimo |
| Avaria Particular | C | PRO | Marítimo |
| Benefício | F | SEG | |
| Bilhete de Seguro | F | SEG | |
| Boa-fé e Má-fé | F | PRO | Um verbete; incluir Dolo |
| Caducidade | F | PRO | |
| Carregamento do Prêmio | F | PRO | Junto com Prêmio Puro |
| Carta Azul (RCTR-VI) | C | PRO | |
| Casco | F/C | PRO | Auto, marítimo e aeronáutico |
| Cédula do Produto Rural (CPR) e seu seguro | C | PRO | |
| Certificado de Seguro | F | SEG | |
| Cláusula e Cláusula Adicional | F | PRO | Um verbete |
| Cláusula Beneficiária | F | PRO | |
| Cláusula de Rateio | S | PRO | |
| Cobertura Parcial Temporária (CPT) | S/C | SEG | |
| Condições Gerais | F | SEG | |
| Cooperativa Médica e Odontológica | C | PRO | |
| Corretagem (comissão) | S | PRO | |
| Dano (Corporal, Material e Moral) | F | SEG | Um verbete com os três tipos |
| Denúncia | F | PRO | |
| Depreciação | F | SEG | |
| Desconto de Fidelidade | F | SEG | |
| Diária por Internação Hospitalar | C | SEG | |
| Diárias por Incapacidade Temporária (DIT) | C | SEG | |
| Doenças Graves (cobertura) | C | SEG | |
| Dupla Indenização | F | SEG | |
| Equipamento | F | PRO | Contexto auto/carga |
| Exclusões (Riscos Excluídos) | S | SEG | |
| Extensão de Garantia de Automóvel | C | SEG | |
| Garantido | C | PRO | Riscos financeiros |
| IFPD | C | SEG | |
| ILPD | C | SEG | |
| Invalidez Permanente | F | SEG | |
| Invalidez Permanente por Acidente (IPA) | C | SEG | |
| IOF sobre o Seguro | S | SEG | |
| Jurisprudência | F | PRO | |
| Lei dos Grandes Números | F | PRO | Absorve "Probabilidades" |
| Limite Técnico | F | PRO | |
| Medicina de Grupo | C | PRO | |
| Migração e Adaptação de Contrato | C | SEG | Planos de saúde |
| Morte Voluntária | F | PRO | Termo sensível, ver regra editorial 7 |
| Nota de Seguro | F | PRO | |
| Penalidade | F | PRO | |
| Perda de Renda (cobertura) | C | SEG | |
| PGBL | S/C | SEG | |
| Plano Ambulatorial x Hospitalar | C | SEG | Segmentações de cobertura |
| Planos Dotais (Dotal Puro e Misto) | C | PRO | |
| Preposto | F | PRO | |
| Prêmio Adicional | F | PRO | |
| Prêmio Fracionado | F | SEG | |
| Primeiro Risco Absoluto x Segundo Risco | F | PRO | Um verbete |
| Pro-Rata | F | PRO | |
| Pulverização do Risco | F | PRO | |
| RCF-V (Responsabilidade Civil Facultativa de Veículos) | S/C | SEG | |
| RCs do Transportador | C | PRO | Verbete guarda-chuva com RCTR-C, RCA-C, RCTA-C, RCTF-C, RCOTM-C, RCTI-C e RCF-DC como seções |
| Registro Geral de Apólice | F | PRO | |
| Remissão | C | SEG | Planos de saúde |
| Representante de Seguros | S | PRO | |
| Resgate | F | SEG | Previdência e capitalização |
| RETA | C | PRO | Aeronáutico |
| Riscos de Engenharia | C | PRO | |
| Riscos Diversos | C | PRO | |
| Riscos Nomeados x Riscos Operacionais (RN, RO, RNO) | C | PRO | |
| Rol de Procedimentos e Eventos em Saúde | C | SEG | ANS |
| Seguro Aquícola | C | PRO | |
| Seguro Auto por Assinatura | S | SEG | |
| Seguro Compreensivo de Florestas | C | PRO | |
| Seguro de Crédito (Interno e à Exportação) | C | PRO | |
| Seguro de Uso (Pay-per-use) | S | SEG | |
| Seguro Educacional | C | SEG | |
| Seguro em Grupo | F | PRO | |
| Seguro Empresarial | S/C | PRO | |
| Seguro Patrimonial | S/C | PRO | |
| Seguro Penhor Rural | C | PRO | |
| Seguro Popular (peças usadas) | S | SEG | |
| Seguro Social x Seguros Privados | F | PRO | Um verbete |
| SFH (Sistema Financeiro de Habitação) | C | PRO | |
| Tábua de Mortalidade | F | PRO | |
| Tarifa | F | PRO | |
| Tipos de Renda (previdência) | C | SEG | |
| Título de Capitalização | S/C | SEG | As 6 modalidades (Tradicional, Instrumento de Garantia, Popular, Filantropia Premiável, Incentivo, Compra Programada) como seções de um verbete só |
| VGBL | S/C | SEG | |
| Vício Próprio | F | PRO | Atenção: a definição da fonte Fenacor está errada (texto duplicado de outro verbete); redigir do zero |

**Cortados da base Fenacor** (fracos ou serviço de apólice, não conceito): Localização e Envio de Peças, Orientação Jurídica Não Contenciosa, Bilateral, Veículo Recuperado, Veículo Reparado, Extinção Contratual (absorvido por Cancelamento/Vigência), Carroceria (absorvido por Casco).

---

## 3. Arquitetura técnica no WordPress + Elementor

### 3.1 Stack necessária

| Item | Ferramenta | Custo | Função |
|---|---|---|---|
| Theme Builder | Elementor Pro | já previsto no projeto | Template do verbete (single) |
| Campos customizados | ACF (versão gratuita) | grátis | Campos estruturados do verbete |
| Custom Post Type | Snippet PHP (abaixo) via plugin Code Snippets | grátis | Post type `glossario` |
| Página índice /glossario/ | Shortcode PHP (abaixo) + CSS/JS | grátis | Agrupamento A-Z + busca instantânea |
| Linkagem automática | Internal Link Juicer (gratuito) | grátis | Primeira ocorrência de cada termo vira link |
| Schema | Snippet PHP (abaixo) | grátis | DefinedTerm + FAQPage + Breadcrumb |
| SEO geral | RankMath (já previsto) | grátis | Title, description, sitemap do CPT |

Recomendação: usar o plugin **Code Snippets** para todo PHP customizado, em vez do functions.php do tema. Snippets sobrevivem à troca de tema e podem ser desativados individualmente se algo quebrar.

### 3.2 Registro do Custom Post Type

Criar um snippet no Code Snippets com o código abaixo (executar em "Run snippet everywhere"):

```php
add_action('init', function () {
    register_post_type('glossario', [
        'labels' => [
            'name'          => 'Glossário',
            'singular_name' => 'Verbete',
            'add_new_item'  => 'Adicionar verbete',
            'edit_item'     => 'Editar verbete',
        ],
        'public'       => true,
        'has_archive'  => false, // o índice será uma página com shortcode
        'rewrite'      => ['slug' => 'glossario', 'with_front' => false],
        'menu_icon'    => 'dashicons-book-alt',
        'supports'     => ['title', 'editor', 'revisions'],
        'show_in_rest' => true, // necessário para o editor e para o Elementor
    ]);

    // Taxonomia interna para o leitor-alvo (facilita filtros e relatórios)
    register_taxonomy('leitor_alvo', 'glossario', [
        'label'        => 'Leitor-alvo',
        'hierarchical' => false,
        'public'       => false,
        'show_ui'      => true,
        'show_in_rest' => true,
    ]);
});

// Regravar as regras de URL uma vez após ativar o snippet:
// Painel > Configurações > Links permanentes > Salvar alterações
```

Depois de ativar, ir em Configurações > Links Permanentes e clicar em Salvar (isso regrava as regras de rewrite e faz `/glossario/franquia/` funcionar).

### 3.3 Campos no ACF (especificação corrigida e completa, 100% versão gratuita)

Criar um grupo de campos "Verbete do Glossário" com regra de localização "Tipo de post é igual a glossario". Regra geral: o redator só preenche campos, nunca escreve no editor de blocos nem toca em HTML/estilo. Cores, cards e layout vêm do template do Elementor.

| Nome do campo | Tipo | Instrução para o redator |
|---|---|---|
| `definicao_direta` | WYSIWYG (toolbar básica, sem botão de mídia) | 40 a 60 palavras, linguagem do leitor-alvo. Negrito permitido no trecho-chave. Em verbetes SEG: proibido usar palavra que seja verbete do glossário. |
| `tambem_aparece_como` | Texto | Sinônimos e termo em inglês, separados por vírgula |
| `revisor_tecnico` | Usuário | Selecionar o usuário do WordPress; o template gera o link para a página de autor automaticamente |
| `como_funciona` | WYSIWYG | Texto corrido da seção "Como funciona na prática". Links para outros verbetes e negrito permitidos. |
| `exemplo_titulo` | Texto | Ex.: "Exemplo com números" |
| `exemplo_passo_1` | Textarea | Passo 1 do exemplo prático |
| `exemplo_passo_2` | Textarea | Passo 2 |
| `exemplo_passo_3` | Textarea | Passo 3 (opcional) |
| `tipos_titulo` | Texto | Título da seção de variações, ex.: "Os três tipos de franquia" (opcional; sem preencher, a seção não aparece) |
| `tipo_1_badge` | Texto | Rótulo do badge, ex.: "Padrão de mercado" |
| `tipo_1_cor` | Select (azul / âmbar / verde) | Cor do badge |
| `tipo_1_titulo` | Texto | Nome do tipo |
| `tipo_1_texto` | Textarea | Descrição curta |
| `tipo_2_badge` / `tipo_2_cor` / `tipo_2_titulo` / `tipo_2_texto` | idem | Card 2 (opcional) |
| `tipo_3_badge` / `tipo_3_cor` / `tipo_3_titulo` / `tipo_3_texto` | idem | Card 3 (opcional) |
| `confusao_titulo` | Texto | Ex.: "A confusão mais comum" (opcional) |
| `confusao_texto` | WYSIWYG | Texto do bloco; links para outros verbetes permitidos |
| `faq_pergunta_1` / `faq_resposta_1` | Texto / Textarea | FAQ 1 |
| `faq_pergunta_2` / `faq_resposta_2` | Texto / Textarea | FAQ 2 |
| `faq_pergunta_3` / `faq_resposta_3` | Texto / Textarea | FAQ 3 (opcional) |
| `secao_extra_titulo` | Texto | Seção coringa para verbetes que precisem de um bloco a mais (opcional) |
| `secao_extra_texto` | WYSIWYG | Conteúdo da seção coringa |
| `tag_noticias` | Texto (slug) | Slug da tag que alimenta o bloco "no noticiário" |

Notas importantes:
1. O Repeater do ACF Pro NÃO é necessário. Repeater serve para repetição ilimitada; as estruturas do verbete têm máximo conhecido (3 passos, 3 tipos, 3 FAQs), o que campos fixos resolvem na versão gratuita.
2. No template do Elementor, todo bloco opcional usa condição de exibição "campo não está vazio" (tipos, confusão, seção extra, passo 3, FAQ 3).
3. Nos campos WYSIWYG, o schema (seção 3.6) deve usar wp_strip_all_tags ao montar o JSON-LD, o que o snippet já faz.
4. O campo `revisor_tecnico` como tipo Usuário retorna o objeto do usuário; no template, exibir nome + link com get_author_posts_url.

### 3.4 Template do verbete (Elementor Pro Theme Builder)

1. Templates > Theme Builder > Single > Adicionar novo, condição de exibição: **Glossário (todos)**.
2. Reproduzir o layout do arquivo modelo `glossario-franquia-modelo.html`: cada bloco vira uma seção do Elementor e os textos são widgets de Título/Texto com **Dynamic Tags > ACF Field** apontando para os campos acima.
3. Bloco "no noticiário": widget **Loop Grid** com query por tag, usando o valor de `tag_noticias` (no Elementor Pro, a query do Loop Grid aceita taxonomia dinâmica; se a versão instalada não aceitar, usar um shortcode simples de WP_Query, mesmo padrão do item 3.5).
4. Copiar o CSS dos modelos (classes `.def-card`, `.exemplo-card`, `.confusao`, `.faq`, `.rel-chips`, `.tag-news`) para Site Settings > Custom CSS, assim vale para todos os verbetes.
5. Blocos opcionais (confusão comum, FAQ 3): na seção, usar Condição de exibição do Elementor ("Dynamic > ACF field is not empty") para o bloco só renderizar quando o campo estiver preenchido.

### 3.5 Página índice /glossario/ (shortcode)

Criar a página "Glossário" com slug `glossario` no WordPress (como o CPT tem `has_archive => false`, a página comum pode usar o slug sem conflito). No Elementor, montar o hero e inserir um widget Shortcode com `[safemidia_glossario]`. Snippet:

```php
add_shortcode('safemidia_glossario', function () {
    $q = new WP_Query([
        'post_type'      => 'glossario',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ]);

    if (!$q->have_posts()) return '<p>Os primeiros verbetes chegam em breve.</p>';

    // Agrupa por letra inicial (ignorando acentos)
    $grupos = [];
    foreach ($q->posts as $p) {
        $titulo = get_the_title($p);
        $letra  = mb_strtoupper(mb_substr(remove_accents($titulo), 0, 1));
        if (!preg_match('/[A-Z]/', $letra)) $letra = '#';
        $grupos[$letra][] = $p;
    }
    ksort($grupos);

    $todas = range('A', 'Z');
    $out   = '<nav class="az-bar" aria-label="Navegar por letra"><div class="az-bar-inner">';
    foreach ($todas as $l) {
        $out .= isset($grupos[$l])
            ? '<a href="#letra-' . strtolower($l) . '">' . $l . '</a>'
            : '<a class="off">' . $l . '</a>';
    }
    $out .= '</div></nav>';

    $out .= '<div class="glo-search"><input type="text" id="gloFilter" ';
    $out .= 'placeholder="Busque um termo: franquia, carência, sinistro..." ';
    $out .= 'aria-label="Buscar termo no glossário"></div>';

    foreach ($grupos as $letra => $posts) {
        $out .= '<section class="letra-sec" id="letra-' . strtolower($letra) . '">';
        $out .= '<div class="letra-head"><span class="letra">' . esc_html($letra) . '</span><span class="linha"></span></div>';
        $out .= '<div class="termos-grid">';
        foreach ($posts as $p) {
            $def    = get_field('definicao_direta', $p->ID);
            $leitor = wp_get_post_terms($p->ID, 'leitor_alvo', ['fields' => 'names']);
            $badge  = !empty($leitor) ? $leitor[0] : '';
            $out .= '<a class="termo-card" href="' . esc_url(get_permalink($p)) . '">';
            if ($badge) {
                $cls  = (mb_strtolower($badge) === 'profissional') ? ' pro' : '';
                $out .= '<span class="termo-tag' . $cls . '">' . esc_html($badge) . '</span>';
            }
            $out .= '<h2>' . esc_html(get_the_title($p)) . '</h2>';
            if ($def) $out .= '<p>' . esc_html(wp_trim_words($def, 22, '...')) . '</p>';
            $out .= '</a>';
        }
        $out .= '</div></section>';
    }

    $out .= '<p class="sem-resultado" id="semResultado" style="display:none">';
    $out .= 'Nenhum termo encontrado. Que tal <a href="/contato/">sugerir a inclusão</a>?</p>';

    return $out;
});
```

CSS do índice (colar em Site Settings > Custom CSS do Elementor, ajustando às variáveis do tema):

```html
<style>
.az-bar { background: #13294F; position: sticky; top: 64px; z-index: 50; border-radius: 12px; margin-bottom: 28px; }
.az-bar-inner { padding: 12px 16px; display: flex; flex-wrap: wrap; gap: 6px; }
.az-bar a { min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,.75); text-decoration: none; }
.az-bar a:hover { background: #1E50D4; color: #fff; }
.az-bar a.off { opacity: .28; pointer-events: none; }
.glo-search { position: relative; max-width: 620px; margin-bottom: 34px; }
.glo-search input { width: 100%; height: 54px; border-radius: 100px; border: 1px solid #DDE2EF; padding: 0 24px; font-size: 15px; outline: none; }
.letra-sec { margin-bottom: 40px; scroll-margin-top: 130px; }
.letra-head { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
.letra-head .letra { font-family: Merriweather, serif; font-size: 28px; font-weight: 900; color: #1E50D4; width: 52px; height: 52px; background: #F4F6FB; border: 1px solid #DDE2EF; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.letra-head .linha { flex: 1; height: 1px; background: #DDE2EF; }
.termos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.termo-card { border: 1px solid #DDE2EF; border-radius: 12px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px; text-decoration: none; transition: all .15s; }
.termo-card:hover { border-color: #1E50D4; box-shadow: 0 4px 18px rgba(11,30,61,.07); }
.termo-card h2 { font-family: Merriweather, serif; font-size: 17px; font-weight: 700; color: #0B1E3D; margin: 0; }
.termo-card p { font-size: 13px; line-height: 1.6; color: #5C6E8A; margin: 0; }
.termo-tag { display: inline-block; width: fit-content; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; padding: 3px 9px; border-radius: 100px; background: #E3EAFB; color: #1E50D4; }
.termo-tag.pro { background: #FAEEDA; color: #854F0B; }
@media (max-width: 900px) { .termos-grid { grid-template-columns: 1fr; } .az-bar-inner { overflow-x: auto; flex-wrap: nowrap; } }
</style>
```

JavaScript da busca instantânea (widget HTML do Elementor na mesma página, abaixo do shortcode):

```html
<script>
(function () {
  var input = document.getElementById('gloFilter');
  if (!input) return;
  var cards  = Array.prototype.slice.call(document.querySelectorAll('.termo-card'));
  var secoes = Array.prototype.slice.call(document.querySelectorAll('.letra-sec'));
  var vazio  = document.getElementById('semResultado');

  function normaliza(s) {
    return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  input.addEventListener('input', function () {
    var q = normaliza(input.value.trim());
    var total = 0;
    cards.forEach(function (card) {
      var mostra = !q || normaliza(card.textContent).indexOf(q) !== -1;
      card.style.display = mostra ? '' : 'none';
      if (mostra) total++;
    });
    secoes.forEach(function (sec) {
      var visiveis = sec.querySelectorAll('.termo-card:not([style*="none"])').length;
      sec.style.display = visiveis ? '' : 'none';
    });
    if (vazio) vazio.style.display = total ? 'none' : 'block';
  });
})();
</script>
```

### 3.6 Schema (DefinedTerm + FAQPage + Breadcrumb)

RankMath e Yoast não geram `DefinedTerm` nativamente. Snippet que monta o JSON-LD a partir dos campos ACF e injeta no head dos verbetes:

```php
add_action('wp_head', function () {
    if (!is_singular('glossario')) return;

    $id  = get_the_ID();
    $url = get_permalink($id);

    $graph = [];

    $graph[] = [
        '@type'            => 'DefinedTerm',
        '@id'              => $url . '#termo',
        'name'             => get_the_title($id),
        'description'      => wp_strip_all_tags(get_field('definicao_direta', $id)),
        'inDefinedTermSet' => [
            '@type' => 'DefinedTermSet',
            'name'  => 'Glossário do Seguro Safe Mídia',
            'url'   => home_url('/glossario/'),
        ],
    ];

    // FAQ: só entra se houver ao menos uma pergunta preenchida
    $faqs = [];
    for ($i = 1; $i <= 3; $i++) {
        $p = get_field("faq_pergunta_$i", $id);
        $r = get_field("faq_resposta_$i", $id);
        if ($p && $r) {
            $faqs[] = [
                '@type'          => 'Question',
                'name'           => wp_strip_all_tags($p),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags($r)],
            ];
        }
    }
    if ($faqs) $graph[] = ['@type' => 'FAQPage', 'mainEntity' => $faqs];

    $graph[] = [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início',    'item' => home_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Glossário', 'item' => home_url('/glossario/')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => get_the_title($id)],
        ],
    ];

    echo '<script type="application/ld+json">' .
         wp_json_encode(['@context' => 'https://schema.org', '@graph' => $graph],
             JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
         '</script>' . "\n";
}, 20);
```

Atenção: se o RankMath também gerar um FAQPage para a mesma página (via bloco de FAQ), manter só um dos dois para não duplicar o tipo.

### 3.7 Linkagem automática (Internal Link Juicer) e política de links

Configuração fechada do plugin (aba Conteúdo e limites):

1. Tipos de post onde o plugin INSERE links: Posts e os CPTs `glossario` e `boletim_regulatorio`. Remover Páginas.
2. Tipos de post ALVO de links: apenas o CPT `glossario`.
3. Quantidade máxima de links por post: **4**.
4. Frequência máxima com que um post é vinculado dentro de outro: **1** (primeira ocorrência).
5. Ordem das palavras-chave: **"maior número de palavras primeiro"** (garante que "seguro garantia" vença "garantia" e o link vá para o verbete mais específico).
6. "Criar links com a maior frequência possível": desligado (ligado, ignora todos os limites).
7. Modo case-sensitive: desligado. Excluir áreas HTML: Títulos (h1-h6).
8. Em cada verbete publicado, cadastrar as keywords no metabox: o termo e variações reais (ex.: Franquia: "franquia", "franquias", "franquia do seguro").
9. Não criar keyword para palavras de uso comum soltas ("risco", "evento", "dano"); usar variações compostas ("agravação do risco" sim; "risco" não).
10. Entidades ultra frequentes (Susep, ANS, CNseg) ficam FORA da automação; o caminho delas é tag + verbete.
11. Após migrar de ambiente local para o domínio definitivo, rodar o rebuild do índice do plugin.
12. Revisão mensal do relatório: keyword que gera links demais indica termo genérico demais.

Complementos da política (fora do plugin):

- **Categorias**: linkadas por snippet PHP próprio (não pelo Link Juicer, cujo suporte a taxonomias é Pro): máximo 2 por post, nunca a própria categoria da matéria. Teto prático total: ~5 auto-links por matéria.
- **Mapa único de de-para**: planilha com termo, variações, URL alvo e coluna "mecanismo" (Link Juicer ou snippet). Nenhuma expressão cadastrada nos dois. Conflito verbete x categoria: o verbete vence.
- **Links externos**: sempre manuais, apontando para o documento primário citado (deep link: a página da consulta pública, o estudo, o edital no DOU; nunca a home da instituição), um por fonte por matéria, rel normal para fontes institucionais, nofollow/sponsored só para conteúdo comercial.
- **Redação não linka manualmente termos do glossário** (evita colisão com a automação, já que a detecção de links manuais é recurso Pro); link manual é reservado a matérias e fontes externas.

### 3.8 SEO e ajustes finais

1. **RankMath > Titles & Meta > Glossário**: habilitar o CPT no sitemap; template de título "%title% (seguros): o que é e como funciona | Glossário Safe Mídia".
2. **Meta description por verbete**: usar a `definicao_direta` como base (RankMath permite variável de campo ACF ou preencher manualmente).
3. **Breadcrumbs visuais** no template do Elementor (o schema já sai do snippet 3.6).
4. **Página índice**: title "Glossário do Seguro: termos explicados em linguagem simples", com texto introdutório de 2 parágrafos acima do shortcode.
5. **Interlinks de saída**: cada verbete linka a categoria correspondente do portal (ex.: Franquia > Auto e Mobilidade) para distribuir autoridade também no sentido verbete > editoria.

---

## 4. Fontes de referência (consulta, nunca cópia)

1. Glossário Fenacor: fenacor.org.br/InformacoesAoPublico/GlossarioDeSeguros
2. Glossário do Mercado Segurador CNseg (PDF atualizado em outubro/2025): página cnseg.org.br/publicacoes/glossario-do-mercado-segurador, botão Download
3. Glossário oficial Susep e normativos (checagem de definições legais)

---

## 5. Checklist de lançamento do glossário

- [ ] CPT `glossario` registrado e links permanentes regravados
- [ ] Grupo de campos ACF criado com instruções de preenchimento visíveis
- [ ] Template single no Theme Builder com blocos condicionais funcionando
- [ ] Página /glossario/ com shortcode, CSS e busca instantânea
- [ ] Snippet de schema ativo e validado no Rich Results Test do Google
- [ ] Internal Link Juicer configurado com limites definidos
- [ ] CPT incluído no sitemap do RankMath
- [ ] Onda 1 (46 verbetes) redigida, revisada tecnicamente e publicada
- [ ] Keywords da onda 1 cadastradas no Internal Link Juicer
- [ ] Guia editorial (seção 1 deste documento) compartilhado com a redação
- [ ] Medição configurada: filtro de páginas /glossario/ no GA4 e no Search Console

## 6. Como medir sucesso (métricas honestas)

1. **Impressões no Search Console** dos verbetes (aparecer na SERP importa mais que clique, dado o cenário de AI Overviews)
2. **Citações em IA**: teste mensal perguntando definições ao ChatGPT, Gemini e Perplexity e registrando quando o Safe Mídia é citado
3. **Páginas por sessão** de quem entra por matéria e navega para verbete (comprova a malha funcionando)
4. **Links externos recebidos** pelos verbetes (Ahrefs/Search Console)
5. Ignorar: sessões brutas do glossário como métrica principal
