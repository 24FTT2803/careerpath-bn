{{--
    Editing a role's duties as the tree they are.

    They were a textarea, one duty per line, which could hold a
    flat list and nothing else. The book is not flat: a duty can
    be generic to the role or specific to one of its job
    families, and under some families it heads a list of
    sub-duties. So the textarea showed the generic ones only —
    which is why deleting a duplicate there left its copies
    standing wherever they were nested, and the form and the data
    said different things.

    Expects $tree (the nested rows) and $jobFamilies (the role's
    own families). Posts the whole tree as JSON in one hidden
    field, because a nested structure in form field names is a
    parser nobody wants to maintain.
--}}
<div class="form-group" x-data="workFunctionTree(@js($tree))">
    <label>Critical Work Functions</label>
    <small class="form-text">
        Every duty this role performs, at every level. A duty specific to one of
        the role's job families says so; the rest apply however the role is
        practised. Three levels deep, which is as deep as the framework goes.
    </small>

    <input type="hidden" name="work_functions" :value="JSON.stringify(nodes)">

    <div class="tree">
        <template x-for="(node, index) in nodes" :key="node.key">
            <div class="node depth-1">
                <div class="node-row">
                    <span class="handle"><i class="fas fa-grip-lines"></i></span>
                    <textarea class="form-control" rows="2"
                              x-model="node.text"
                              placeholder="What the role does"></textarea>
                    <div class="node-actions">
                        <button type="button" @click="moveUp(nodes, index)" :disabled="index === 0" title="Move up">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button type="button" @click="moveDown(nodes, index)" :disabled="index === nodes.length - 1" title="Move down">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button type="button" @click="addChild(node)" title="Add a sub-duty">
                            <i class="fas fa-level-down-alt"></i>
                        </button>
                        <button type="button" class="is-danger" @click="remove(nodes, index)" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                {{--
                    Only at the top of a list. What nests under a
                    family's duty belongs to that family too, and
                    saying so again on every child is how the two
                    come to disagree.
                --}}
                <div class="node-scope">
                    <label>
                        <input type="checkbox"
                               :checked="node.kind === 'specific'"
                               @change="setKind(node, $event.target.checked)">
                        Specific to a job family
                    </label>

                    <select x-show="node.kind === 'specific'" x-model="node.job_family_id" class="form-control">
                        <option value="">Which family?</option>
                        @foreach($jobFamilies as $family)
                            <option value="{{ $family->id }}">{{ $family->name }}</option>
                        @endforeach
                    </select>

                    <span class="no-families" x-show="node.kind === 'specific' && {{ $jobFamilies->count() }} === 0">
                        This role has no job families recorded, so there is nothing to file it under.
                    </span>
                </div>

                <div class="children">
                    <template x-for="(child, childIndex) in node.children" :key="child.key">
                        <div class="node depth-2">
                            <div class="node-row">
                                <span class="handle"><i class="fas fa-angle-right"></i></span>
                                <textarea class="form-control" rows="2"
                                          x-model="child.text"
                                          placeholder="A duty under the one above"></textarea>
                                <div class="node-actions">
                                    <button type="button" @click="moveUp(node.children, childIndex)" :disabled="childIndex === 0" title="Move up">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" @click="moveDown(node.children, childIndex)" :disabled="childIndex === node.children.length - 1" title="Move down">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <button type="button" @click="addChild(child)" title="Add a sub-duty">
                                        <i class="fas fa-level-down-alt"></i>
                                    </button>
                                    <button type="button" class="is-danger" @click="remove(node.children, childIndex)" title="Remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="children">
                                <template x-for="(leaf, leafIndex) in child.children" :key="leaf.key">
                                    <div class="node depth-3">
                                        <div class="node-row">
                                            <span class="handle"><i class="fas fa-circle"></i></span>
                                            <textarea class="form-control" rows="2"
                                                      x-model="leaf.text"
                                                      placeholder="A sub-duty"></textarea>
                                            <div class="node-actions">
                                                <button type="button" @click="moveUp(child.children, leafIndex)" :disabled="leafIndex === 0" title="Move up">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                                <button type="button" @click="moveDown(child.children, leafIndex)" :disabled="leafIndex === child.children.length - 1" title="Move down">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                                <button type="button" class="is-danger" @click="remove(child.children, leafIndex)" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <p class="tree-empty" x-show="nodes.length === 0">
        No duties recorded for this role yet.
    </p>

    <button type="button" class="btn btn-outline btn-sm add-top" @click="addTop()">
        <i class="fas fa-plus"></i> Add a duty
    </button>

    <p class="tree-count">
        <span x-text="count(nodes)"></span> in all. Empty rows are dropped when you save.
    </p>
</div>

<script>
    window.workFunctionTree = function (tree) {
        /*
         * A key per node, for x-for to track rows by. The row's
         * position cannot do it: reordering or removing one then
         * reuses the key of whatever moved into its place, and
         * the textareas keep the text of the node that was there
         * before.
         */
        let next = 1;

        const keyed = function (nodes) {
            return (nodes || []).map(function (node) {
                return {
                    key: next++,
                    text: node.text || '',
                    kind: node.kind || 'generic',
                    job_family_id: node.job_family_id === null || node.job_family_id === undefined
                        ? ''
                        : String(node.job_family_id),
                    children: keyed(node.children),
                };
            });
        };

        return {
            nodes: keyed(tree),

            blank() {
                return { key: next++, text: '', kind: 'generic', job_family_id: '', children: [] };
            },

            addTop() {
                this.nodes.push(this.blank());
            },

            addChild(node) {
                node.children.push(this.blank());
            },

            remove(list, index) {
                /*
                 * A node takes its children with it. Keeping them
                 * would promote a sub-duty into a duty of the
                 * role, which is a different statement.
                 */
                list.splice(index, 1);
            },

            moveUp(list, index) {
                if (index > 0) {
                    list.splice(index - 1, 0, list.splice(index, 1)[0]);
                }
            },

            moveDown(list, index) {
                if (index < list.length - 1) {
                    list.splice(index + 1, 0, list.splice(index, 1)[0]);
                }
            },

            setKind(node, specific) {
                node.kind = specific ? 'specific' : 'generic';

                if (!specific) {
                    node.job_family_id = '';
                }
            },

            count(nodes) {
                return (nodes || []).reduce(
                    (total, node) => total + 1 + this.count(node.children),
                    0
                );
            },
        };
    };
</script>

<style>
    .tree { margin-top: 10px; }
    .tree .node { margin-bottom: 10px; }
    .tree .node-row { display: flex; align-items: flex-start; gap: 8px; }
    .tree .handle { padding-top: 10px; color: #c7cbd1; font-size: 11px; }
    .tree .depth-1 > .node-row > .handle { color: #c9a84c; font-size: 13px; }
    .tree .depth-3 > .node-row > .handle { font-size: 6px; padding-top: 16px; }
    .tree textarea.form-control { flex: 1; line-height: 1.6; resize: vertical; }
    .tree .node-actions { display: flex; gap: 4px; padding-top: 4px; }
    .tree .node-actions button { width: 30px; height: 30px; border: 1px solid #e5e7eb; background: white; border-radius: 6px; color: #6b7280; font-size: 11px; cursor: pointer; }
    .tree .node-actions button:hover:not(:disabled) { border-color: #c9a84c; color: #1a3a5c; }
    .tree .node-actions button:disabled { opacity: .35; cursor: default; }
    .tree .node-actions button.is-danger:hover { border-color: #c65b4e; color: #c65b4e; }
    .tree .node-scope { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 6px 0 0 26px; font-size: 12px; color: #6b7280; }
    .tree .node-scope label { display: flex; align-items: center; gap: 6px; font-weight: 500; margin: 0; color: #6b7280; }
    .tree .node-scope select { min-width: 220px; padding: 5px 10px; font-size: 13px; }
    .tree .no-families { color: #c65b4e; }
    .tree .children { margin: 8px 0 0 34px; border-left: 2px solid #f3f4f6; padding-left: 12px; }
    .tree-empty { margin: 10px 0; font-size: 13px; color: #6b7280; font-style: italic; }
    .add-top { margin-top: 6px; }
    .tree-count { margin: 8px 0 0; font-size: 12px; color: #6b7280; }
</style>
