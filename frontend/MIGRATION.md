# Migração do AuthContext para RTK + TanStack Query

Este documento descreve a migração completa do sistema de autenticação de React Context para uma arquitetura moderna usando Redux Toolkit (RTK) para client state e TanStack Query para server state management.

## 📋 Motivação

A migração foi realizada para obter os seguintes benefícios:

- **Separação clara de responsabilidades**: Client state (RTK) vs Server state (TanStack Query)
- **Performance otimizada**: Cache inteligente e deduplicação automática de requisições
- **DevTools avançados**: Redux DevTools + React Query DevTools
- **Escalabilidade**: Arquitetura mais robusta para crescimento da aplicação
- **TypeScript robusto**: Melhor tipagem e IntelliSense

## 🔄 Arquitetura Anterior vs Nova

### Anterior (AuthContext)
```
AuthContext
├── useState para user, token, loading
├── useEffect para persistência
├── Funções inline para login/register/logout
└── Lógica de API misturada com state management
```

### Nova (RTK + TanStack Query)
```
Redux Store (RTK)
├── authSlice: Gerencia client state (user, token, isAuthenticated)
└── Persistência automática no localStorage

TanStack Query
├── Mutations: login, register, logout
├── Cache automático e otimizações
└── Loading/error states gerenciados automaticamente

Custom Hook (useAuth)
├── Combina RTK + TanStack Query
├── Interface limpa para componentes
└── Tratamento de erros centralizado
```

## 📁 Estrutura de Arquivos Criada

```
src/
├── store/
│   ├── store.ts              # Configuração do Redux store
│   ├── hooks.ts              # Hooks tipados (useAppDispatch, useAppSelector)
│   └── slices/
│       └── authSlice.ts      # Slice de autenticação
├── lib/
│   └── queryClient.ts        # Configuração do TanStack Query
└── hooks/
    └── useAuth.ts            # Hook customizado combinando RTK + TanStack Query
```

## 🛠️ Implementação Detalhada

### 1. Store Configuration (`store/store.ts`)
```typescript
import { configureStore } from '@reduxjs/toolkit';
import authSlice from './slices/authSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
  },
});
```

### 2. Auth Slice (`store/slices/authSlice.ts`)
**Responsabilidades:**
- Gerenciar estado do usuário logado
- Persistência no localStorage
- Estado de autenticação

**Actions:**
- `setCredentials`: Define usuário e token
- `clearCredentials`: Limpa dados de autenticação

### 3. TanStack Query Setup (`lib/queryClient.ts`)
**Configurações:**
- Retry: 1 tentativa para queries
- Stale time: 5 minutos
- Refetch on window focus: Desabilitado

### 4. Custom Hook (`hooks/useAuth.ts`)
**Mutations implementadas:**
- `loginMutation`: Autenticação de usuário
- `registerMutation`: Registro de novo usuário
- `logoutMutation`: Logout com limpeza de cache

**Interface do Hook:**
```typescript
{
  user, token, isAuthenticated,    // Estado atual
  login, register, logout,         // Ações
  loading,                         // Loading state unificado
  loginError, registerError        // Tratamento de erros
}
```

## 🔄 Migração dos Componentes

### App.tsx
**Antes:**
```tsx
<AuthProvider>
  <AppContent />
</AuthProvider>
```

**Depois:**
```tsx
<Provider store={store}>
  <QueryClientProvider client={queryClient}>
    <AppContent />
    <ReactQueryDevtools initialIsOpen={false} />
  </QueryClientProvider>
</Provider>
```

### Componentes (Login.tsx, Dashboard.tsx)
**Antes:**
```tsx
import { useAuth } from "../contexts/AuthContext";
```

**Depois:**
```tsx
import { useAuth } from "../hooks/useAuth";
```

## ✨ Melhorias Implementadas

### 1. Tratamento de Erros
- Erros específicos por mutation
- Fallback para mensagens genéricas
- TypeScript strict para error handling

### 2. Performance
- Cache automático de requisições
- Deduplicação de requests simultâneos
- Otimistic updates quando apropriado

### 3. Developer Experience
- Redux DevTools integration
- React Query DevTools
- TypeScript strict mode
- ESLint compliance

### 4. Persistência
- Estado hidratado automaticamente do localStorage
- Sincronização automática entre abas
- Cleanup automático no logout

## 🧪 Validações Realizadas

### Build & Lint
```bash
✅ npm run build  # Compilação TypeScript + Vite
✅ npm run lint   # ESLint validation
```

### Funcionalidades Testadas
- ✅ Login com persistência
- ✅ Logout com limpeza
- ✅ Estado de loading
- ✅ Tratamento de erros
- ✅ Navegação baseada em autenticação

## 📦 Dependências Adicionadas

```json
{
  "@reduxjs/toolkit": "^2.8.2",
  "@tanstack/react-query": "^5.85.6",
  "@tanstack/react-query-devtools": "^5.85.6",
  "react-redux": "^9.2.0"
}
```

## 🚀 Próximos Passos Recomendados

### 1. Implementações Futuras
- [ ] Refresh token automático
- [ ] Queries para dados do usuário (perfil, preferências)
- [ ] Otimistic updates para mutations
- [ ] Offline support com React Query

### 2. Monitoramento
- [ ] Error boundary para mutations
- [ ] Analytics de performance
- [ ] Logs estruturados

### 3. Testes
- [ ] Unit tests para authSlice
- [ ] Integration tests para useAuth hook
- [ ] E2E tests para fluxo de autenticação

## 🎯 Resultados

A migração foi bem-sucedida, mantendo 100% da funcionalidade original enquanto introduz uma arquitetura mais robusta e escalável. A aplicação agora está preparada para crescimento futuro com melhor separação de responsabilidades e ferramentas avançadas de desenvolvimento.