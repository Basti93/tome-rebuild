export function filterByGroups(users, groups) {
    return users.filter(a => groups.some(({id}) => a.groups.map(ag => ag.id).includes(id)))
}
