# 📄 **Sistema de Relatórios de Folha Salarial - PDF**

## 🆕 **Funcionalidades Implementadas**

### 📊 **1. Relatório Geral da Folha Salarial**

**Características:**
- **Header profissional** com logo da empresa e dados institucionais
- **Layout similar às faturas** para manter consistência visual
- **Agrupamento por departamento** para melhor organização
- **Resumo financeiro completo** com totais e subtotais
- **Informações detalhadas** de cada técnico

**Conteúdo do Relatório:**
```
🏢 HEADER DA EMPRESA
├─ Logo Areia Branca
├─ Dados institucionais (NUIT, endereço, telefone)

📋 DETALHES DO PROCESSO
├─ Título e período (mês/ano)
├─ Status e datas de processamento/aprovação
├─ Responsáveis pelo processamento

📊 RESUMO GERAL
├─ Total de técnicos
├─ Salários base consolidados
├─ Horas extras e valores
├─ Bônus totais
├─ Descontos gerais
└─ Valor líquido total

🏢 DETALHAMENTO POR DEPARTAMENTO
├─ Lista de técnicos por departamento
├─ Subtotais por departamento
├─ Cálculos individuais detalhados
└─ Observações específicas

💰 RESUMO FINAL
├─ Total bruto (base + extras + bônus)
├─ Total de descontos
└─ TOTAL LÍQUIDO A PAGAR
```

### 💼 **2. Payslip Individual do Técnico**

**Características:**
- **Documento personalizado** para cada técnico
- **Informações completas do funcionário** (nome, código, departamento)
- **Detalhamento de ganhos e descontos**
- **Seção específica para faltas** com cálculos detalhados
- **Explicação dos cálculos** (taxa horária, fórmulas)

**Conteúdo do Payslip:**
```
👤 INFORMAÇÕES DO FUNCIONÁRIO
├─ Nome completo
├─ Código do funcionário
├─ Departamento e área
├─ Data de processamento
└─ Status do pagamento

💰 SEÇÃO DE GANHOS
├─ Salário base
├─ Horas extras (quantidade e valor)
├─ Bônus
└─ Total de ganhos

📉 SEÇÃO DE DESCONTOS
├─ Descontos por faltas (detalhado)
├─ Outros descontos
└─ Total de descontos

⚠️ DETALHES DAS FALTAS (se houver)
├─ Tabela com data, tipo, horas, motivo
├─ Cálculo individual por falta
└─ Total de descontos por faltas

💚 SALÁRIO LÍQUIDO
├─ Valor final destacado
└─ Cálculo explicativo

📊 DETALHAMENTO DO CÁLCULO
├─ Taxa horária (salário ÷ 160h)
├─ Taxa de hora extra (1.5x)
├─ Fórmula final aplicada
└─ Explicações complementares
```

## 🚀 **Como Usar**

### **1. Relatório Geral da Folha**
1. Acesse a página de **visualização** de um processo salarial
2. Clique no botão **"Baixar Folha Salarial"**
3. O PDF será gerado e baixado automaticamente
4. Nome do arquivo: `folha-salarial-{mês}-{ano}.pdf`

### **2. Payslip Individual**
1. Na tabela de técnicos, clique no botão **"Payslip"** do técnico desejado
2. O PDF individual será gerado e baixado
3. Nome do arquivo: `payslip-{nome-tecnico}-{mês}-{ano}.pdf`

### **3. Todos os Payslips**
1. No rodapé da tabela, clique no botão **"Todos"**
2. Múltiplas abas serão abertas (uma para cada técnico)
3. Intervalo de 500ms entre downloads para evitar sobrecarga

## 💡 **Integração com Sistema de Faltas**

### **Cálculo Automático de Descontos:**
```
💰 Taxa Horária = Salário Base ÷ 160h
📉 Dedução por Falta = Horas Perdidas × Taxa Horária
✅ Apenas faltas APROVADAS são consideradas
🗓️ Filtro automático por mês/ano do processamento
```

### **Exibição no Payslip:**
- **Tabela detalhada** com cada falta
- **Data, tipo, horas e motivo** de cada ausência  
- **Cálculo individual** por evento
- **Total consolidado** dos descontos

## 🎨 **Design e Layout**

### **Consistência Visual:**
- ✅ **Mesmo header** das faturas existentes
- ✅ **Paleta de cores** corporativa
- ✅ **Tipografia** padronizada
- ✅ **Estrutura** profissional

### **Responsividade:**
- 📱 **Otimizado para impressão**
- 🖥️ **Visualização em tela**
- 📄 **Formato A4** padrão

## 🔧 **Implementação Técnica**

### **Backend (Laravel):**
```php
// Relatório geral
Route::get('salary-processes/{id}/report', 'generateReport');

// Payslip individual  
Route::get('salary-processes/{processId}/payslip/{itemId}', 'generatePayslip');
```

### **Frontend (Vue.js):**
```javascript
// Baixar folha salarial completa
const generateReport = () => {
    window.open(`/salary-processes/${id}/report`, '_blank');
}

// Baixar payslip individual
const generatePayslip = (itemId, name) => {
    window.open(`/salary-processes/${id}/payslip/${itemId}`, '_blank');
}
```

### **Templates (Blade):**
- `resources/views/admin/salary-processes/report.blade.php`
- `resources/views/admin/salary-processes/payslip.blade.php`

## 📋 **Checklist de Funcionalidades**

- ✅ **Relatório PDF da folha salarial** com header empresarial
- ✅ **Payslips individuais** com detalhamento completo
- ✅ **Integração com sistema de faltas** (cálculo automático)
- ✅ **Agrupamento por departamento** no relatório geral
- ✅ **Botões de download** na interface
- ✅ **Geração de todos os payslips** com um clique
- ✅ **Layout profissional** consistente com faturas
- ✅ **Cálculos explicativos** nos payslips
- ✅ **Validação de status** do processo salarial
- ✅ **Nomenclatura automática** dos arquivos

## 🚀 **Resultado Final**

O sistema agora oferece uma **solução completa** para geração de documentos da folha salarial:

1. **Substituição do JSON** por PDFs profissionais
2. **Documentos individuais** para cada técnico
3. **Integração total** com o sistema de faltas existente
4. **Interface intuitiva** com botões específicos
5. **Qualidade empresarial** nos documentos gerados

### 🎯 **Benefícios:**
- 📈 **Profissionalização** dos documentos
- ⚡ **Agilidade** na geração de relatórios  
- 🔍 **Transparência** nos cálculos
- 🤝 **Satisfação** dos funcionários
- 📊 **Controle** administrativo aprimorado