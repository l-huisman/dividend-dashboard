import { ref, watch } from 'vue'

const expanded = ref(localStorage.getItem('sidebarExpanded') === 'true')

watch(expanded, (val) => localStorage.setItem('sidebarExpanded', val))

export function useSidebar() {
  return { expanded }
}
