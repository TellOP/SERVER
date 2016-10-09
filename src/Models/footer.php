<?php
/* Copyright © 2016 University of Murcia
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
?>
<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/h5f.min.js"></script>
<script src="/js/validator.min.js"></script>
<script src="/js/l10n.min.js"></script>
<script src="/js/l10ninit.js.php?<?php echo $_SESSION['language']; ?>"></script>
<script src="/js/app.js"></script>
<?php /** @noinspection PhpUndefinedVariableInspection */
foreach ($additionalincludes as $script) {
    echo "<script src=\"$script\"></script>\n";
}
?>
</body>
</html>
